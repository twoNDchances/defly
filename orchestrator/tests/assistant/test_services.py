from types import SimpleNamespace
from unittest.mock import AsyncMock, Mock, patch

from django.core.exceptions import ImproperlyConfigured
from django.test import SimpleTestCase, override_settings
from langchain_core.messages import AIMessage, HumanMessage, SystemMessage
from langchain_openai import ChatOpenAI

from app.assistant.prompts import system_prompt
from app.assistant.services.ai import AssistantService
from app.bases.exceptions import AssistantServiceError


class AssistantServiceTests(SimpleTestCase):
    @override_settings(AI_API_KEY="")
    def test_model_requires_api_key(self):
        with self.assertRaisesMessage(
            ImproperlyConfigured,
            "AI_API_KEY is not configured.",
        ):
            AssistantService.model()

    @override_settings(
        AI_API_KEY="test-key",
        AI_BASE_URL="https://ai.example.test/v1",
        AI_MODEL="test-model",
        AI_TIMEOUT=42,
    )
    def test_model_uses_settings(self):
        model = AssistantService.model()

        self.assertIsInstance(model, ChatOpenAI)
        self.assertEqual("test-model", model.model_name)
        self.assertEqual(42, model.request_timeout)
        self.assertEqual("https://ai.example.test/v1/", str(model.root_client.base_url))

    @override_settings(AI_MAX_MESSAGE_CHARACTERS=5)
    async def test_chat_converts_messages_and_response_without_tools(self):
        model = Mock()
        model.ainvoke = AsyncMock(return_value=AIMessage(content=" Reply "))
        messages = [
            {
                "role": "user",
                "content": "Question",
                "resources": [{"data": {"description": "R" * 30}}],
            },
            {"role": "assistant", "content": "Earlier"},
        ]

        with patch.object(AssistantService, "model", return_value=model):
            result = await AssistantService.chat(messages)

        self.assertEqual("Reply", result)
        converted = model.ainvoke.await_args.args[0]
        self.assertIsInstance(converted[0], SystemMessage)
        self.assertIsInstance(converted[1], HumanMessage)
        self.assertIsInstance(converted[2], AIMessage)
        self.assertTrue(converted[1].content.startswith("Quest\n\n"))
        self.assertIn("R" * 30, converted[1].content)
        self.assertEqual("Earli", converted[2].content)
        self.assertFalse(hasattr(AssistantService, "agent"))
        self.assertEqual(["ainvoke"], [item[0] for item in model.method_calls])

    async def test_chat_rejects_empty_response(self):
        model = Mock()
        model.ainvoke = AsyncMock(return_value=AIMessage(content=[]))

        with (
            patch.object(AssistantService, "model", return_value=model),
            self.assertRaisesMessage(
                AssistantServiceError,
                "The AI provider returned an empty response.",
            ),
        ):
            await AssistantService.chat([{"role": "user", "content": "Hi"}])

    async def test_chat_wraps_provider_errors_and_preserves_configuration_errors(self):
        provider = Mock()
        provider.ainvoke = AsyncMock(side_effect=RuntimeError("provider failed"))

        with (
            patch.object(AssistantService, "model", return_value=provider),
            self.assertRaisesMessage(AssistantServiceError, "could not complete"),
        ):
            await AssistantService.chat([{"role": "user", "content": "Hi"}])

        with (
            patch.object(
                AssistantService,
                "model",
                side_effect=ImproperlyConfigured("missing key"),
            ),
            self.assertRaisesMessage(ImproperlyConfigured, "missing key"),
        ):
            await AssistantService.chat([{"role": "user", "content": "Hi"}])

    def test_content_blocks_are_normalized(self):
        self.assertEqual("plain", AssistantService._content_to_text("plain"))
        self.assertEqual(
            "first\nsecond",
            AssistantService._content_to_text(
                ["first", {"text": "second"}, {"type": "ignored"}]
            ),
        )

    def test_resources_are_added_to_user_message_content(self):
        resources = [
            {
                "type": "label",
                "id": "resource-id",
                "label": "Suspicious traffic",
                "data": {"name": "Suspicious traffic"},
            }
        ]

        content = AssistantService._content_with_resources(
            "Review this resource",
            resources,
        )

        self.assertIn("<defly_resources>", content)
        self.assertIn('"type": "label"', content)
        self.assertIn('"name": "Suspicious traffic"', content)
        self.assertTrue(content.startswith("Review this resource"))
        self.assertEqual(
            "Review this resource",
            AssistantService._content_with_resources("Review this resource", []),
        )

    def test_system_prompt_defines_no_tool_boundary_and_attachment_context(self):
        prompt = system_prompt()

        self.assertIn("Role and scope:", prompt)
        self.assertIn("No tools are available", prompt)
        self.assertIn("<defly_resources>", prompt)
        self.assertIn("must not present any data mutation as completed", prompt)
        self.assertIn("Defly execution model:", prompt)
        self.assertIn("Decision.action is the Decision's own enum field", prompt)
        self.assertIn("type=report directly to the Rule", prompt)
        self.assertIn("same-phase Rules as an AND group", prompt)
        self.assertIn("never word_file", prompt)
        self.assertNotIn("model-tool catalog", prompt)
        self.assertNotIn("manage_label", prompt)

    @override_settings(AI_MAX_MESSAGE_CHARACTERS=5)
    def test_message_content_is_limited_by_configured_character_count(self):
        self.assertEqual("12345", AssistantService._limit_message_content("123456789"))

    @override_settings(AI_MAX_MESSAGE_CHARACTERS=0)
    def test_zero_message_character_limit_is_unlimited(self):
        self.assertEqual(
            "123456789",
            AssistantService._limit_message_content("123456789"),
        )

    @override_settings(AI_MAX_MESSAGES=2)
    async def test_load_messages_uses_configured_limit(self):
        class MessageQuery:
            ordering = ()
            limit = None

            def order_by(self, *fields):
                self.ordering = fields
                return self

            def __getitem__(self, item):
                self.limit = item
                return self

            def __aiter__(self):
                async def messages():
                    yield SimpleNamespace(role="assistant", content="Newer")
                    yield SimpleNamespace(
                        role="user",
                        content="Older",
                        resources=[{"type": "label", "id": "resource-id"}],
                    )

                return messages()

        query = MessageQuery()
        with patch(
            "app.assistant.services.ai.Message.objects.filter",
            return_value=query,
        ) as filter_messages:
            messages = await AssistantService.load_messages("conversation-id")

        filter_messages.assert_called_once_with(conservation_id="conversation-id")
        self.assertEqual(("-created_at", "-id"), query.ordering)
        self.assertEqual(slice(None, 2), query.limit)
        self.assertEqual(
            [
                {
                    "role": "user",
                    "content": "Older",
                    "resources": [{"type": "label", "id": "resource-id"}],
                },
                {"role": "assistant", "content": "Newer"},
            ],
            messages,
        )

    @override_settings(AI_MAX_MESSAGES=0)
    async def test_zero_message_count_limit_loads_all_messages(self):
        class UnlimitedMessageQuery:
            ordering = ()

            def order_by(self, *fields):
                self.ordering = fields
                return self

            def __getitem__(self, item):
                raise AssertionError("Unlimited message queries must not be sliced.")

            def __aiter__(self):
                async def messages():
                    yield SimpleNamespace(role="assistant", content="Newer")
                    yield SimpleNamespace(role="user", content="Older")

                return messages()

        query = UnlimitedMessageQuery()
        with patch(
            "app.assistant.services.ai.Message.objects.filter",
            return_value=query,
        ):
            messages = await AssistantService.load_messages("conversation-id")

        self.assertEqual(("-created_at", "-id"), query.ordering)
        self.assertEqual(
            [
                {"role": "user", "content": "Older"},
                {"role": "assistant", "content": "Newer"},
            ],
            messages,
        )
