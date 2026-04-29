from datetime import UTC, datetime, timedelta
from ipaddress import ip_address
from pathlib import Path

from cryptography import x509
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.x509.oid import ExtendedKeyUsageOID, NameOID
from django.conf import settings
from django.core.management.base import BaseCommand


class Command(BaseCommand):
    help = "Generate self-signed TLS certificate and key for orchestrator."

    certificate_name = "orchestrator.crt"
    key_name = "orchestrator.key"
    valid_days = 365
    key_size = 2048
    public_exponent = 65537

    common_name = "orchestrator"
    organization_name = "Defly Orchestrator"
    dns_names = ("localhost", "orchestrator")
    ip_addresses = ("127.0.0.1",)

    def handle(self, *args, **options):
        tls_directory = Path(settings.BASE_DIR) / "storage" / "tls"
        certificate_file, key_file = self.generate_tls_certificate_pair(tls_directory)

        self.stdout.write(
            self.style.SUCCESS(f"TLS certificate written to {certificate_file}")
        )
        self.stdout.write(self.style.SUCCESS(f"TLS key written to {key_file}"))

    def generate_tls_certificate_pair(self, tls_directory: Path) -> tuple[Path, Path]:
        tls_directory.mkdir(parents=True, exist_ok=True)
        certificate_file = (tls_directory / self.certificate_name).resolve()
        key_file = (tls_directory / self.key_name).resolve()

        private_key = rsa.generate_private_key(
            public_exponent=self.public_exponent,
            key_size=self.key_size,
        )
        certificate = self.generate_self_signed_certificate(private_key)

        key_file.write_bytes(
            private_key.private_bytes(
                encoding=serialization.Encoding.PEM,
                format=serialization.PrivateFormat.TraditionalOpenSSL,
                encryption_algorithm=serialization.NoEncryption(),
            )
        )
        certificate_file.write_bytes(
            certificate.public_bytes(serialization.Encoding.PEM)
        )

        key_file.chmod(0o600)
        certificate_file.chmod(0o644)

        return certificate_file, key_file

    def generate_self_signed_certificate(
        self,
        private_key: rsa.RSAPrivateKey,
    ) -> x509.Certificate:
        now = datetime.now(UTC)
        subject = issuer = x509.Name(
            [
                x509.NameAttribute(NameOID.ORGANIZATION_NAME, self.organization_name),
                x509.NameAttribute(NameOID.COMMON_NAME, self.common_name),
            ]
        )
        subject_alt_names = [
            *(x509.DNSName(name) for name in self.dns_names),
            *(x509.IPAddress(ip_address(address)) for address in self.ip_addresses),
        ]

        return (
            x509.CertificateBuilder()
            .subject_name(subject)
            .issuer_name(issuer)
            .public_key(private_key.public_key())
            .serial_number(x509.random_serial_number())
            .not_valid_before(now - timedelta(hours=1))
            .not_valid_after(now + timedelta(days=self.valid_days))
            .add_extension(
                x509.BasicConstraints(ca=False, path_length=None),
                critical=True,
            )
            .add_extension(
                x509.KeyUsage(
                    digital_signature=True,
                    content_commitment=False,
                    key_encipherment=True,
                    data_encipherment=False,
                    key_agreement=False,
                    key_cert_sign=False,
                    crl_sign=False,
                    encipher_only=None,
                    decipher_only=None,
                ),
                critical=True,
            )
            .add_extension(
                x509.ExtendedKeyUsage([ExtendedKeyUsageOID.SERVER_AUTH]),
                critical=False,
            )
            .add_extension(
                x509.SubjectAlternativeName(subject_alt_names),
                critical=False,
            )
            .sign(private_key, hashes.SHA256())
        )
