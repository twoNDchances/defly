from django.conf import settings
from django.urls import include, path

urlpatterns = [
    path(
        f"{getattr(settings, 'SERVER_PATH_PREFIX', 'api/v1')}/",
        include("app.bases.urls"),
    ),
]
