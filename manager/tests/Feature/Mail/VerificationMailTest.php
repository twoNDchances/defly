<?php

namespace Tests\Feature\Mail;

use App\Mail\VerificationMail;
use Tests\TestCase;

class VerificationMailTest extends TestCase
{
    public function test_verification_mail_uses_expected_subject_view_and_attachments(): void
    {
        $mail = new VerificationMail('Tester', 'tester@example.com', 'token-value');

        $this->assertSame('Verification Mail', $mail->envelope()->subject);
        $this->assertSame('mails.verification', $mail->content()->view);
        $this->assertSame([], $mail->attachments());
    }
}
