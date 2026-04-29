from pathlib import Path

from django.conf import settings
from django.core.management.base import BaseCommand, CommandError
from django.core.management.utils import get_random_secret_key


class Command(BaseCommand):
    help = "Generate Django SECRET_KEY and write it to SECRET_KEY_FILE."

    def handle(self, *args, **options):
        secret_key_file_setting = getattr(settings, "SECRET_KEY_FILE", None)
        if not secret_key_file_setting:
            raise CommandError("SECRET_KEY_FILE is not configured in settings.")

        secret_key_file = Path(secret_key_file_setting)
        if not secret_key_file.is_absolute():
            secret_key_file = Path(settings.BASE_DIR) / secret_key_file

        secret_key_file = secret_key_file.resolve()
        secret_key_file.parent.mkdir(parents=True, exist_ok=True)
        secret_key_file.write_text(f"{get_random_secret_key()}\n", encoding="utf-8")

        self.stdout.write(
            self.style.SUCCESS(f"SECRET_KEY written to {secret_key_file}")
        )
