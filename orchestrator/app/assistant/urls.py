from django.urls import path

from app.assistant.views.mai import ChatView

urlpatterns = [
    path("<uuid:conservation_id>", ChatView.as_view(), name="assistant-chat"),
]
