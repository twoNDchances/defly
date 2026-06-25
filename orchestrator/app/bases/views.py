from django.conf import settings
from django.http import HttpRequest, HttpResponseNotAllowed
from django.views import View


class ConfiguredMethodView(View):
    view_is_async = True
    method_handler_names: dict[str, str] = {}

    def get_method_handlers(self):
        return {
            str(getattr(settings, setting_name)).strip().lower(): getattr(
                self,
                handler_name,
            )
            for setting_name, handler_name in self.method_handler_names.items()
        }

    async def dispatch(self, request: HttpRequest, *args, **kwargs):
        method_handlers = self.get_method_handlers()
        handler = method_handlers.get(request.method.lower())
        if handler is None:
            allowed_methods = sorted(method.upper() for method in method_handlers)
            return HttpResponseNotAllowed(allowed_methods)

        return await handler(request, *args, **kwargs)
