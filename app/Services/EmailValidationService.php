<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmailValidationService
{
    /**
     * List of common disposable email domains
     */
    private static array $disposableDomains = [
        '10minutemail.com',
        '10minutemail.net',
        'tempmail.org',
        'guerrillamail.com',
        'mailinator.com',
        'yopmail.com',
        'temp-mail.org',
        'throwaway.email',
        'getnada.com',
        'maildrop.cc',
        'mohmal.com',
        'mailnesia.com',
        'sharklasers.com',
        'guerrillamailblock.com',
        'anonymizer.com',
        'antispam.de',
        'bigstring.com',
        'binkmail.com',
        'bugmenot.com',
        'chogmail.com',
        'deadaddress.com',
        'despam.it',
        'discardmail.com',
        'disposableemailaddresses.com',
        'disposableinbox.com',
        'dispose.it',
        'dodgeit.com',
        'dontreg.com',
        'dumpyemail.com',
        'emailias.com',
        'emailinfive.com',
        'emailmiser.com',
        'emailsensei.com',
        'emailtemporanea.com',
        'fakeinbox.com',
        'fakemailz.com',
        'filzmail.com',
        'front14.org',
        'getonemail.com',
        'hidemail.de',
        'hotpop.com',
        'ieatspam.eu',
        'inboxalias.com',
        'incognitomail.com',
        'jetable.com',
        'jetable.org',
        'keepmymail.com',
        'killmail.com',
        'kurzepost.de',
        'letthemeatspam.com',
        'litedrop.com',
        'lookugly.com',
        'mailcatch.com',
        'mailde.de',
        'mailexpire.com',
        'mailforspam.com',
        'mailfreeonline.com',
        'mailguard.me',
        'mailinator.net',
        'mailinator.org',
        'mailmetrash.com',
        'mailmoat.com',
        'mailnull.com',
        'mailscrap.com',
        'mailshell.com',
        'mailsiphon.com',
        'mailtome.de',
        'mailtrash.net',
        'mailzilla.com',
        'meltmail.com',
        'mintemail.com',
        'mytrashmail.com',
        'neomailbox.com',
        'neverbox.com',
        'no-spam.ws',
        'nobulk.com',
        'noclickemail.com',
        'nomail2me.com',
        'nomorespamemails.com',
        'nonspammer.de',
        'nospammail.net',
        'notmailinator.com',
        'nowmymail.com',
        'objectmail.com',
        'oneoffemail.com',
        'onewaymail.com',
        'otherinbox.com',
        'owlpic.com',
        'pookmail.com',
        'privacy.net',
        'privatdemail.net',
        'quickinbox.com',
        'rcpt.at',
        'reallymymail.com',
        'reconmail.com',
        'recyclebin.jp',
        'safe-mail.net',
        'safersignup.de',
        'safetymail.info',
        'safetypost.de',
        'selfdestructingmail.com',
        'sendspamhere.de',
        'shieldedmail.com',
        'shortmail.net',
        'sneakemail.com',
        'sofort-mail.de',
        'spam.la',
        'spam.su',
        'spam4.me',
        'spamail.de',
        'spambob.com',
        'spambog.com',
        'spambox.us',
        'spamcannon.com',
        'spamday.com',
        'spamex.com',
        'spamfree24.com',
        'spamgourmet.com',
        'spamhole.com',
        'spaminator.de',
        'spamkill.info',
        'spaml.com',
        'spammotel.com',
        'spamobox.com',
        'spamoff.de',
        'spamslicer.com',
        'spamspot.com',
        'spamthis.co.uk',
        'spamtroll.net',
        'stuffmail.de',
        'supergreatmail.com',
        'suremail.info',
        'talkinator.com',
        'temp-mail.ru',
        'tempalias.com',
        'tempemail.com',
        'tempinbox.com',
        'tempmail.eu',
        'tempmailer.com',
        'temporaryemail.net',
        'temporaryinbox.com',
        'temporarymailaddress.com',
        'thanksnospam.info',
        'thisisnotmyrealemail.com',
        'throwawayemailaddresses.com',
        'trash-mail.com',
        'trash-mail.de',
        'trashdevil.com',
        'trashemail.de',
        'trashmail.at',
        'trashmail.com',
        'trashmail.de',
        'trashmail.net',
        'trashmail.org',
        'trashmailer.com',
        'trashymail.com',
        'uggsrock.com',
        'wegwerfmail.de',
        'wegwerfmail.net',
        'willselfdestruct.com',
        'wronghead.com',
        'yopmail.fr',
        'yopmail.net',
        'youmailr.com',
        'zehnminutenmail.de',
        'zippymail.info'
    ];

    /**
     * Validate email address comprehensively
     *
     * @param string $email
     * @return array
     */
    public static function validateEmail(string $email): array
    {
        // Format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'message' => 'Invalid email format. Please enter a valid email address.',
                'error_type' => 'format'
            ];
        }

        // Extract domain
        $domain = substr(strrchr($email, "@"), 1);

        // Check for disposable email
        if (self::isDisposableEmail($domain)) {
            return [
                'valid' => false,
                'message' => 'Temporary or disposable email addresses are not allowed. Please use a permanent email address.',
                'error_type' => 'disposable'
            ];
        }

        // DNS MX record check
        if (!checkdnsrr($domain, 'MX')) {
            return [
                'valid' => false,
                'message' => 'Email domain does not exist or has no mail server. Please check your email address.',
                'error_type' => 'domain'
            ];
        }

        // Additional domain validation
        if (!self::isValidDomain($domain)) {
            return [
                'valid' => false,
                'message' => 'Email domain appears to be invalid. Please use a valid email address.',
                'error_type' => 'domain'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Email address is valid',
            'error_type' => null
        ];
    }

    /**
     * Check if email domain is disposable
     *
     * @param string $domain
     * @return bool
     */
    private static function isDisposableEmail(string $domain): bool
    {
        $domain = strtolower($domain);
        return in_array($domain, self::$disposableDomains);
    }

    /**
     * Additional domain validation
     *
     * @param string $domain
     * @return bool
     */
    private static function isValidDomain(string $domain): bool
    {
        // Check domain format
        if (!preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain)) {
            return false;
        }

        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/', // IP addresses
            '/\.test$/', // Test domains
            '/\.local$/', // Local domains
            '/\.localhost$/', // Localhost domains
            '/\.example\.(com|org|net)$/', // Example domains
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $domain)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verify email with SMTP check (basic implementation)
     *
     * @param string $email
     * @return bool
     */
    public static function smtpVerify(string $email): bool
    {
        try {
            $domain = substr(strrchr($email, "@"), 1);
            $mxRecords = [];

            if (!getmxrr($domain, $mxRecords)) {
                return false;
            }

            // For production, implement actual SMTP verification
            // For now, we return true if MX records exist
            return !empty($mxRecords);
        } catch (\Exception $e) {
            Log::warning('SMTP verification failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
