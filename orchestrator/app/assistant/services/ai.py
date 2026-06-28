from json import dumps

from django.conf import settings
from django.core.exceptions import ImproperlyConfigured
from langchain_core.messages import AIMessage, BaseMessage, HumanMessage, SystemMessage
from langchain_openai import ChatOpenAI

from app.assistant.models import Message
from app.assistant.prompts import system_prompt
from app.bases.exceptions import AssistantServiceError
from app.bases.models import User


class AssistantService:
    @staticmethod
    def model() -> ChatOpenAI:
        api_key = str(settings.AI_API_KEY).strip()
        if not api_key:
            raise ImproperlyConfigured("AI_API_KEY is not configured.")

        return ChatOpenAI(
            api_key=api_key,
            base_url=settings.AI_BASE_URL,
            model=settings.AI_MODEL,
            max_retries=1,
            timeout=settings.AI_TIMEOUT,
        )

    @classmethod
    async def chat(
        cls,
        messages: list[dict[str, object]],
        *,
        user: User | None = None,
    ) -> str:
        del user

        try:
            response = await cls.model().ainvoke(cls._to_langchain_messages(messages))
        except ImproperlyConfigured:
            raise
        except Exception as exception:
            raise AssistantServiceError(
                "The AI provider could not complete the request."
            ) from exception

        if not isinstance(response, AIMessage):
            raise AssistantServiceError("The AI provider returned an empty response.")

        content = cls._content_to_text(response.content).strip()
        if not content:
            raise AssistantServiceError("The AI provider returned an empty response.")

        return content

    @staticmethod
    async def load_messages(conservation_id: str) -> list[dict[str, object]]:
        query = Message.objects.filter(conservation_id=conservation_id).order_by(
            "-created_at", "-id"
        )
        if settings.AI_MAX_MESSAGES > 0:
            query = query[: settings.AI_MAX_MESSAGES]

        messages = [
            AssistantService._message_payload(message) async for message in query
        ]
        messages.reverse()

        return messages

    @staticmethod
    def _message_payload(message: Message) -> dict[str, object]:
        payload: dict[str, object] = {
            "role": message.role,
            "content": message.content,
        }
        resources = getattr(message, "resources", None)
        if isinstance(resources, list) and resources:
            payload["resources"] = resources

        return payload

    @staticmethod
    def _limit_message_content(content: str) -> str:
        limit = settings.AI_MAX_MESSAGE_CHARACTERS

        return content if limit == 0 else content[:limit]

    @staticmethod
    def _content_with_resources(content: str, resources: object) -> str:
        if not isinstance(resources, list) or not resources:
            return content

        serialized = dumps(resources, ensure_ascii=False, default=str)

        return (
            f"{content}\n\n"
            "Attached Defly system resources (JSON):\n"
            f"<defly_resources>{serialized}</defly_resources>"
        )

    @staticmethod
    def _to_langchain_messages(
        messages: list[dict[str, object]],
    ) -> list[BaseMessage]:
        message_types = {
            "assistant": AIMessage,
            "user": HumanMessage,
        }

        return [
            SystemMessage(content=system_prompt()),
            *[
                message_types[str(message["role"])](
                    content=AssistantService._content_with_resources(
                        AssistantService._limit_message_content(
                            str(message["content"])
                        ),
                        message.get("resources"),
                    )
                )
                for message in messages
            ],
        ]

    @staticmethod
    def _content_to_text(content: str | list[str | dict]) -> str:
        if isinstance(content, str):
            return content

        parts: list[str] = []
        for block in content:
            if isinstance(block, str):
                parts.append(block)
                continue

            text = block.get("text")
            if isinstance(text, str):
                parts.append(text)

        return "\n".join(parts)
