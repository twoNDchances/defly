from django.urls import path

from app.deployments.views.defender import DefenderView

urlpatterns = [
    path("<uuid:defender_id>", DefenderView.as_view(), name="defender"),
]
