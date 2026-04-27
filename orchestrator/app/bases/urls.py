from django.conf import settings
from django.urls import include, path

urlpatterns = [
    path(
        f"{getattr(settings, 'SERVER_PATH_DEPLOYMENT', 'deployments')}/",
        include("app.deployments.urls"),
    ),
]
