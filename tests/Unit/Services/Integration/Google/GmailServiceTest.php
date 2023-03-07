<?php

namespace Tests\Unit\Services\Integration\Google;

use App\Exceptions\Common\MissingFolderException;
use App\Exceptions\Integration\Google\FailedSendGmailMessageException;
use App\Exceptions\Integration\Google\InvalidGmailAuthMessageException;
use App\Exceptions\Integration\Google\InvalidGoogleAuthCodeException;
use App\Exceptions\Integration\Google\InvalidToEmailAddressException;
use App\Exceptions\Integration\Google\MissingGapiIdTokenException;
use App\Exceptions\Integration\Google\MissingGmailLabelsException;
use App\Exceptions\Integration\Google\MissingGapiAccessTokenException;
use App\Exceptions\PropertyDoesNotExists;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GmailService;
use App\Services\Integration\Google\GoogleServiceInterface;
use Google\Service\Gmail;
use Google\Service\Gmail\ListLabelsResponse;
use Google\Service\Gmail\ListMessagesResponse;
use Google\Service\Gmail\Message;
use Google\Service\Gmail\ModifyMessageRequest;
use Google\Service\Gmail\Profile;
use Google\Service\Gmail\Resource\Users;
use Google_Client;
use Google_Service_Gmail;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use Mockery;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Tests\TestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

/**
 * Test for App\Services\Integration\GmailService
 *
 * Class GmailServiceTest
 * @package Tests\Unit\Services\Integration\Google
 *
 * @coversDefaultClass \App\Services\Integration\Google\GmailService
 */
class GmailServiceTest extends TestCase
{
    private const AUTH_CODE = 'some_auth_code';
    private const REDIRECT_URL = 'some_redirect_url.com';

    private const ACCESS_TOKEN_ACCESS_TOKEN = 'access_token_access_token';
    private const ACCESS_TOKEN_REFRESH_TOKEN = 'access_token_refresh_token';
    private const ACCESS_TOKEN_ID_TOKEN = 'access_token_id_token';
    private const ACCESS_TOKEN_EXPIRES_IN = 123456;
    private const ACCESS_TOKEN_ISSUED_AT = '2022-01-01 00:00:00';
    private const ACCESS_TOKEN_SCOPE = 'access_token scope';
    private const ACCESS_TOKEN_DEALER_ID = PHP_INT_MAX - 123456;

    private const PARSED_EMAIL_ID = PHP_INT_MAX;
    private const PARSED_MESSAGE_ID = PHP_INT_MAX - 1;
    private const PARSED_EMAIL_TO = 'parsed_email@to.com';
    private const PARSED_EMAIL_TO_NAME = 'parsed_email_to_name';
    private const PARSED_EMAIL_FROM = 'parsed_email@from.com';
    private const PARSED_EMAIL_FROM_NAME = 'parsed_email_from_name';
    private const PARSED_EMAIL_SUBJECT = 'parsed_email_subject';
    private const PARSED_EMAIL_BODY = 'parsed_email_body';

    private const GMAIL_MESSAGE_ID = PHP_INT_MAX - 2;
    private const SECOND_GMAIL_MESSAGE_ID = PHP_INT_MAX - 3;

    private const FIRST_LABEL_ID = PHP_INT_MAX - 4;
    private const FIRST_LABEL_NAME = 'first_label_name';
    private const SECOND_LABEL_ID = PHP_INT_MAX - 5;
    private const SECOND_LABEL_NAME = 'second_label_name';

    private const GMAIL_PROFILE_EMAIL_ADDRESS = 'gmail_profile_email_address@test.com';

    private const MAIL_ID = 'some_mail_id';

    private const MESSAGE_HEADER_MESSAGE_ID = 'message_header_message_id';
    private const MESSAGE_HEADER_TO = 'message_header_to@test.com';
    private const MESSAGE_HEADER_TO_NAME = 'message_header_to_name';
    private const MESSAGE_HEADER_FROM = 'message_header_from@test.com';
    private const MESSAGE_HEADER_FROM_NAME = 'message_header_from_name';
    private const MESSAGE_HEADER_SUBJECT = 'message_header_subject';
    private const MESSAGE_BODY_DATA = 'message_body_data';

    /**
     * @var LegacyMockInterface|MockInterface|Manager
     */
    private $fractal;

    /**
     * @var LegacyMockInterface|MockInterface|GoogleServiceInterface
     */
    private $googleService;

    /**
     * @var LegacyMockInterface|MockInterface|GmailService
     */
    private $gmailService;

    /**
     * @var InteractionEmailServiceInterface
     */
    private $interactionEmailService;

    public function setUp(): void
    {
        parent::setUp();

        $this->googleService = Mockery::mock(GoogleServiceInterface::class);
        $this->interactionEmailService = Mockery::mock(InteractionEmailServiceInterface::class);
        $this->fractal = Mockery::mock(Manager::class);

        $this->fractal
            ->shouldReceive('setSerializer')
            ->passthru();

        $this->gmailService = Mockery::mock(GmailService::class, [
            $this->interactionEmailService,
            $this->googleService,
            $this->fractal
        ]);

        $this->gmailService->log = Mockery::mock(LoggerInterface::class);
    }

    /**
     * @group CRM
     * @covers ::auth
     *
     * @dataProvider authDataProvider
     *
     * @param LegacyMockInterface|MockInterface|Google_Client $googleClient
     * @param string $authCode
     * @param string $redirectUrl
     * @param array $accessToken
     *
     * @throws InvalidGoogleAuthCodeException
     * @throws ReflectionException
     */
    public function testAuth(Google_Client $googleClient, string $authCode, string $redirectUrl, array $accessToken)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setRedirectUri')
            ->once()
            ->with($redirectUrl);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithAuthCode')
            ->once()
            ->with($authCode)
            ->andReturn($accessToken);

        $this->gmailService->log
            ->shouldReceive('info');

        $this->gmailService
            ->shouldReceive('profile')
            ->with((Mockery::on(function($emailToken) use ($accessToken) {
                return $emailToken instanceof EmailToken
                    && $emailToken->accessToken === $accessToken['access_token']
                    && $emailToken->refreshToken === $accessToken['refresh_token']
                    && $emailToken->idToken === $accessToken['id_token']
                    && $emailToken->expiresIn === $accessToken['expires_in']
                    && $emailToken->issuedAt === $accessToken['issued_at']
                    && $emailToken->scopes === explode(' ', $accessToken['scope']);
            })))
            ->once()
            ->andReturn(null);

        $this->gmailService
            ->shouldReceive('auth')
            ->passthru();

        $result = $this->gmailService->auth($authCode, $redirectUrl);

        $this->assertInstanceOf(EmailToken::class, $result);

        $this->assertSame($result->accessToken, $accessToken['access_token']);
        $this->assertSame($result->refreshToken, $accessToken['refresh_token']);
        $this->assertSame($result->idToken, $accessToken['id_token']);
        $this->assertSame($result->expiresIn, $accessToken['expires_in']);
        $this->assertSame($result->issuedAt, $accessToken['issued_at']);
        $this->assertSame($result->scopes, explode(' ', $accessToken['scope']));
    }

    /**
     * @group CRM
     * @covers ::auth
     *
     * @dataProvider authDataProvider
     *
     * @param LegacyMockInterface|MockInterface|Google_Client $googleClient
     * @param string $authCode
     * @param string $redirectUrl
     * @param array $accessToken
     *
     * @throws InvalidGoogleAuthCodeException
     * @throws PropertyDoesNotExists
     */
    public function testAuthWithEmailToken(Google_Client $googleClient, string $authCode, string $redirectUrl, array $accessToken)
    {
        $emailToken = new EmailToken([
            'access_token' => self::ACCESS_TOKEN_ACCESS_TOKEN,
            'refresh_token' => self::ACCESS_TOKEN_REFRESH_TOKEN,
            'id_token' => self::ACCESS_TOKEN_ID_TOKEN,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_IN,
            'issued_at' => self::ACCESS_TOKEN_ISSUED_AT,
            'scopes' => explode(' ',self::ACCESS_TOKEN_SCOPE),
        ]);

        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setRedirectUri')
            ->once()
            ->with($redirectUrl);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithAuthCode')
            ->once()
            ->with($authCode)
            ->andReturn($accessToken);

        $this->gmailService->log
            ->shouldReceive('info');

        $this->gmailService
            ->shouldReceive('profile')
            ->with((Mockery::on(function($emailToken) use ($accessToken) {
                return $emailToken instanceof EmailToken
                    && $emailToken->accessToken === $accessToken['access_token']
                    && $emailToken->refreshToken === $accessToken['refresh_token']
                    && $emailToken->idToken === $accessToken['id_token']
                    && $emailToken->expiresIn === $accessToken['expires_in']
                    && $emailToken->issuedAt === $accessToken['issued_at']
                    && $emailToken->scopes === explode(' ', $accessToken['scope']);
            })))
            ->once()
            ->andReturn($emailToken);

        $this->gmailService
            ->shouldReceive('auth')
            ->passthru();

        $result = $this->gmailService->auth($authCode, $redirectUrl);

        $this->assertInstanceOf(EmailToken::class, $result);
        $this->assertEquals($emailToken, $result);

        $this->assertSame($result->accessToken, $accessToken['access_token']);
        $this->assertSame($result->refreshToken, $accessToken['refresh_token']);
        $this->assertSame($result->idToken, $accessToken['id_token']);
        $this->assertSame($result->expiresIn, $accessToken['expires_in']);
        $this->assertSame($result->issuedAt, $accessToken['issued_at']);
        $this->assertSame($result->scopes, explode(' ', $accessToken['scope']));
    }

    /**
     * @group CRM
     * @covers ::auth
     *
     * @dataProvider authDataProvider
     *
     * @param LegacyMockInterface|MockInterface|Google_Client $googleClient
     * @param string $authCode
     * @param string $redirectUrl
     * @param array $accessToken
     *
     * @throws InvalidGoogleAuthCodeException
     * @throws ReflectionException
     */
    public function testAuthWithoutAccessToken(Google_Client $googleClient, string $authCode, string $redirectUrl, array $accessToken)
    {
        unset($accessToken['access_token']);

        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $googleClient
            ->shouldReceive('setRedirectUri')
            ->once()
            ->with($redirectUrl);

        $googleClient
            ->shouldReceive('fetchAccessTokenWithAuthCode')
            ->once()
            ->with($authCode)
            ->andReturn($accessToken);

        $this->gmailService->log
            ->shouldReceive('info');

        $this->expectException(InvalidGoogleAuthCodeException::class);

        $this->gmailService
            ->shouldReceive('profile')
            ->never();

        $this->gmailService
            ->shouldReceive('auth')
            ->passthru();

        $this->gmailService->auth($authCode, $redirectUrl);
    }

    /**
     * @group CRM
     * @covers ::profile
     *
     * @dataProvider profileDataProvider
     *
     * @param EmailToken $emailToken
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|Profile $gmailProfile
     * @throws ReflectionException
     */
    public function testProfile(EmailToken $emailToken, Gmail $googleGmail, Gmail\Profile $gmailProfile)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('setEmailToken')
            ->once()
            ->with($emailToken);

        $googleGmail->users
            ->shouldReceive('getProfile')
            ->once()
            ->with('me')
            ->andReturn($gmailProfile);

        $gmailProfile
            ->shouldReceive('getEmailAddress')
            ->once()
            ->withNoArgs()
            ->andReturn(self::GMAIL_PROFILE_EMAIL_ADDRESS);

        $this->gmailService
            ->shouldReceive('profile')
            ->passthru();

        $result = $this->gmailService->profile($emailToken);

        $this->assertInstanceOf(EmailToken::class, $result);

        $this->assertSame($result->accessToken, $emailToken->accessToken);
        $this->assertSame($result->refreshToken, $emailToken->refreshToken);
        $this->assertSame($result->idToken, $emailToken->idToken);
        $this->assertSame($result->expiresIn, $emailToken->expiresIn);
        $this->assertSame($result->issuedAt, $emailToken->issuedAt);
        $this->assertSame($result->scopes, $emailToken->scopes);
        $this->assertSame($result->emailAddress, self::GMAIL_PROFILE_EMAIL_ADDRESS);
    }

    /**
     * @group CRM
     * @covers ::profile
     *
     * @dataProvider profileDataProvider
     *
     * @param EmailToken $emailToken
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|Profile $gmailProfile
     * @throws ReflectionException
     */
    public function testProfileWithError(EmailToken $emailToken, Gmail $googleGmail, Gmail\Profile $gmailProfile)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('setEmailToken')
            ->once()
            ->with($emailToken);

        $googleGmail->users
            ->shouldReceive('getProfile')
            ->once()
            ->with('me')
            ->andThrow(\Exception::class);

        $gmailProfile
            ->shouldReceive('getEmailAddress')
            ->never();

        $this->gmailService->log
            ->shouldReceive('error');

        $this->gmailService
            ->shouldReceive('profile')
            ->passthru();

        $result = $this->gmailService->profile($emailToken);

        $this->assertNull($result);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @dataProvider sendDataProvider
     *
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testSend(SmtpConfig $smtpConfig, ParsedEmail $parsedEmail, Gmail $googleGmail, Gmail\Message $gmailMessage)
    {
        $attachments = new Collection(['test_attachments']);

        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($smtpConfig->getAccessToken());

        $googleGmail->users_messages
            ->shouldReceive('send')
            ->once()
            ->with('me', (Mockery::on(function($message) {
                return $message instanceof Message
                    && !empty($message->raw)
                    && is_string($message->raw);
            })))
            ->andReturn($gmailMessage);

        $this->gmailService
            ->shouldReceive('message')
            ->once()
            ->with($gmailMessage->id)
            ->andReturn($parsedEmail);

        $this->interactionEmailService
            ->shouldReceive('storeAttachments')
            ->once()
            ->with($smtpConfig->getAccessToken()->dealer_id, $parsedEmail)
            ->andReturn($attachments);

        $this->gmailService
            ->shouldReceive('send')
            ->passthru();

        $result = $this->gmailService->send($smtpConfig, $parsedEmail);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame($parsedEmail->id, $result->id);
        $this->assertSame($parsedEmail->messageId, $result->messageId);
        $this->assertSame($parsedEmail->to, $result->to);
        $this->assertSame($parsedEmail->toName, $result->toName);
        $this->assertSame($parsedEmail->from, $result->from);
        $this->assertSame($parsedEmail->fromName, $result->fromName);
        $this->assertSame($parsedEmail->subject, $result->subject);
        $this->assertSame($parsedEmail->body, $result->body);
        $this->assertSame($parsedEmail->hasAttachments, $result->hasAttachments);
        $this->assertEquals($parsedEmail->attachments, $result->attachments);
        $this->assertSame('Received', $result->direction);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @dataProvider sendDataProvider
     *
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testSendWithInvalidEmailAddress(
        SmtpConfig $smtpConfig,
        ParsedEmail $parsedEmail,
        Gmail $googleGmail,
        Gmail\Message $gmailMessage
    ) {
        $parsedEmail->setTo('wrong_email');

        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($smtpConfig->getAccessToken());

        $this->expectException(InvalidToEmailAddressException::class);

        $googleGmail->users_messages
            ->shouldReceive('send')
            ->never();

        $this->gmailService
            ->shouldReceive('message')
            ->never();

        $this->interactionEmailService
            ->shouldReceive('storeAttachments')
            ->never();

        $this->gmailService
            ->shouldReceive('send')
            ->passthru();

        $this->gmailService->send($smtpConfig, $parsedEmail);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @dataProvider sendDataProvider
     *
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testSendWithInvalidGmailAuth(
        SmtpConfig $smtpConfig,
        ParsedEmail $parsedEmail,
        Gmail $googleGmail,
        Gmail\Message $gmailMessage
    ) {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($smtpConfig->getAccessToken());

        $googleGmail->users_messages
            ->shouldReceive('send')
            ->once()
            ->with('me', (Mockery::on(function($message) {
                return $message instanceof Message
                    && !empty($message->raw)
                    && is_string($message->raw);
            })))
            ->andThrow(new \Exception('invalid authentication'));

        $this->gmailService->log
            ->shouldReceive('error');

        $this->expectException(InvalidGmailAuthMessageException::class);

        $this->gmailService
            ->shouldReceive('message')
            ->never();

        $this->interactionEmailService
            ->shouldReceive('storeAttachments')
            ->never();

        $this->gmailService
            ->shouldReceive('send')
            ->passthru();

        $this->gmailService->send($smtpConfig, $parsedEmail);
    }

    /**
     * @group CRM
     * @covers ::send
     *
     * @dataProvider sendDataProvider
     *
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testSendWithFailedSendGmail(
        SmtpConfig $smtpConfig,
        ParsedEmail $parsedEmail,
        Gmail $googleGmail,
        Gmail\Message $gmailMessage
    ) {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($smtpConfig->getAccessToken());

        $googleGmail->users_messages
            ->shouldReceive('send')
            ->once()
            ->with('me', (Mockery::on(function($message) {
                return $message instanceof Message
                    && !empty($message->raw)
                    && is_string($message->raw);
            })))
            ->andThrow(new \Exception('error'));

        $this->gmailService->log
            ->shouldReceive('error');

        $this->expectException(FailedSendGmailMessageException::class);

        $this->gmailService
            ->shouldReceive('message')
            ->never();

        $this->interactionEmailService
            ->shouldReceive('storeAttachments')
            ->never();

        $this->gmailService
            ->shouldReceive('send')
            ->passthru();

        $this->gmailService->send($smtpConfig, $parsedEmail);
    }

    /**
     * @group CRM
     * @covers ::messages
     *
     * @dataProvider messagesDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ListMessagesResponse $listMessagesResponse
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param array $labels
     * @throws ReflectionException
     */
    public function testMessages(
        AccessToken $accessToken,
        ListMessagesResponse $listMessagesResponse,
        Gmail $googleGmail,
        array $labels
    ) {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $folder = 'some_folder';

        $queryParams = [
            'older' => 'some_value',
            'wrong_key' => 'some_value2',
        ];

        $this->gmailService
            ->shouldReceive('labels')
            ->once()
            ->with($accessToken, [$folder])
            ->andReturn($labels);

        $this->gmailService->log
            ->shouldReceive('info');

        $googleGmail->users_messages
            ->shouldReceive('listUsersMessages')
            ->once()
            ->with('me', [
                'labelIds' => [self::FIRST_LABEL_ID, self::SECOND_LABEL_ID],
                'q' => 'older: some_value'
            ])
            ->andReturn($listMessagesResponse);

        $this->gmailService
            ->shouldReceive('messages')
            ->passthru();

        $result = $this->gmailService->messages($accessToken, $folder, $queryParams);

        $this->assertIsArray($result);
        $this->assertEquals([self::GMAIL_MESSAGE_ID, self::SECOND_GMAIL_MESSAGE_ID], $result);
    }

    /**
     * @group CRM
     * @covers ::messages
     *
     * @dataProvider messagesDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ListMessagesResponse $listMessagesResponse
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param array $labels
     * @throws ReflectionException
     */
    public function testMessagesWithoutMessages(
        AccessToken $accessToken,
        ListMessagesResponse $listMessagesResponse,
        Gmail $googleGmail,
        array $labels
    ) {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $listMessagesResponse->setMessages([]);

        $this->gmailService
            ->shouldReceive('labels')
            ->once()
            ->with($accessToken, ['INBOX'])
            ->andReturn($labels);

        $this->gmailService->log
            ->shouldReceive('info');

        $googleGmail->users_messages
            ->shouldReceive('listUsersMessages')
            ->once()
            ->with('me', [
                'labelIds' => [self::FIRST_LABEL_ID, self::SECOND_LABEL_ID],
                'q' => ''
            ])
            ->andReturn($listMessagesResponse);

        $this->gmailService
            ->shouldReceive('messages')
            ->passthru();

        $result = $this->gmailService->messages($accessToken);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @group CRM
     * @covers ::message
     *
     * @dataProvider messageDataProvider
     *
     * @param string $mailId
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testMessage(string $mailId, Gmail $googleGmail, Message $gmailMessage)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $googleGmail->users_messages
            ->shouldReceive('get')
            ->once()
            ->with('me', $mailId, ['format' => 'full'])
            ->andReturn($gmailMessage);

        $this->gmailService
            ->shouldReceive('message')
            ->passthru();

        $result = $this->gmailService->message($mailId);

        $this->assertInstanceOf(ParsedEmail::class, $result);

        $this->assertSame(self::MAIL_ID, $result->id);
        $this->assertSame(self::MESSAGE_HEADER_MESSAGE_ID, $result->messageId);
        $this->assertSame(self::MESSAGE_HEADER_TO, $result->to);
        $this->assertSame(self::MESSAGE_HEADER_TO_NAME, $result->toName);
        $this->assertSame(self::MESSAGE_HEADER_FROM, $result->from);
        $this->assertSame(self::MESSAGE_HEADER_FROM_NAME, $result->fromName);
        $this->assertSame(self::MESSAGE_HEADER_SUBJECT, $result->subject);
        $this->assertIsString($result->body);
        $this->assertNotEmpty($result->body);
    }

    /**
     * @group CRM
     * @covers ::move
     *
     * @dataProvider moveDataProvider
     *
     * @param string $mailId
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param array $labels
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testMove(string $mailId, Gmail $googleGmail, AccessToken $accessToken, array $labels, Message $gmailMessage)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('labels')
            ->once()
            ->with($accessToken, $labels)
            ->andReturn($labels);

        $googleGmail->users_messages
            ->shouldReceive('modify')
            ->once()
            ->with('me', $mailId, Mockery::on(function($modify) {
                return $modify instanceof ModifyMessageRequest
                    && $modify->getAddLabelIds() === [self::FIRST_LABEL_ID, self::SECOND_LABEL_ID];
            }))
            ->andReturn($gmailMessage);

        $this->gmailService
            ->shouldReceive('move')
            ->passthru();

        $result = $this->gmailService->move($accessToken, $mailId, $labels);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::move
     *
     * @dataProvider moveDataProvider
     *
     * @param string $mailId
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param array $labels
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testMoveUnsuccessful(
        string $mailId,
        Gmail $googleGmail,
        AccessToken $accessToken,
        array $labels,
        Message $gmailMessage
    ) {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('labels')
            ->once()
            ->with($accessToken, $labels)
            ->andReturn($labels);

        $googleGmail->users_messages
            ->shouldReceive('modify')
            ->once()
            ->with('me', $mailId, Mockery::on(function($modify) {
                return $modify instanceof ModifyMessageRequest
                    && $modify->getAddLabelIds() === [self::FIRST_LABEL_ID, self::SECOND_LABEL_ID];
            }))
            ->andReturn(null);

        $this->gmailService
            ->shouldReceive('move')
            ->passthru();

        $result = $this->gmailService->move($accessToken, $mailId, $labels);

        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @covers ::move
     *
     * @dataProvider moveDataProvider
     *
     * @param string $mailId
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param array $labels
     * @param Message $gmailMessage
     * @throws ReflectionException
     */
    public function testMoveWithRemoveLabels(
        string $mailId,
        Gmail $googleGmail,
        AccessToken $accessToken,
        array $labels,
        Message $gmailMessage
    ) {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $removeLabelId1 = PHP_INT_MAX - 123456;
        $removeLabelId2 = PHP_INT_MAX - 654321;

        $removeLabels = [
            [
                'id' => $removeLabelId1
            ],
            [
                'id' => $removeLabelId2
            ]
        ];

        $this->gmailService
            ->shouldReceive('labels')
            ->once()
            ->with($accessToken, $labels)
            ->andReturn($labels);

        $this->gmailService
            ->shouldReceive('labels')
            ->once()
            ->with($accessToken, $removeLabels)
            ->andReturn($removeLabels);

        $googleGmail->users_messages
            ->shouldReceive('modify')
            ->once()
            ->with('me', $mailId, Mockery::on(function($modify) use ($removeLabelId1, $removeLabelId2) {
                return $modify instanceof ModifyMessageRequest
                    && $modify->getAddLabelIds() === [self::FIRST_LABEL_ID, self::SECOND_LABEL_ID]
                    && $modify->getRemoveLabelIds() === [$removeLabelId1, $removeLabelId2];
            }))
            ->andReturn($gmailMessage);

        $this->gmailService
            ->shouldReceive('move')
            ->passthru();

        $result = $this->gmailService->move($accessToken, $mailId, $labels, $removeLabels);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @covers ::labels
     *
     * @dataProvider labelsDataProvider
     *
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ListLabelsResponse $listLabelsResponse
     * @throws ReflectionException
     * @throws MissingFolderException
     * @throws MissingGmailLabelsException
     */
    public function testLabels(Gmail $googleGmail, AccessToken $accessToken, ListLabelsResponse $listLabelsResponse)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($accessToken);

        $googleGmail->users_labels
            ->shouldReceive('listUsersLabels')
            ->once()
            ->with('me')
            ->andReturn($listLabelsResponse);

        $this->gmailService->log
            ->shouldReceive('info');

        $this->gmailService
            ->shouldReceive('labels')
            ->passthru();

        $result = $this->gmailService->labels($accessToken);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertInstanceOf(Gmail\Label::class, $result[0]);
        $this->assertSame(self::FIRST_LABEL_ID, $result[0]->id);
        $this->assertSame(self::FIRST_LABEL_NAME, $result[0]->name);

        $this->assertInstanceOf(Gmail\Label::class, $result[1]);
        $this->assertSame(self::SECOND_LABEL_ID, $result[1]->id);
        $this->assertSame(self::SECOND_LABEL_NAME, $result[1]->name);
    }

    /**
     * @group CRM
     * @covers ::labels
     *
     * @dataProvider labelsDataProvider
     *
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ListLabelsResponse $listLabelsResponse
     * @throws ReflectionException
     * @throws MissingFolderException
     * @throws MissingGmailLabelsException
     */
    public function testLabelsWithoutLabels(Gmail $googleGmail, AccessToken $accessToken, ListLabelsResponse $listLabelsResponse)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $listLabelsResponse->setLabels([]);

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($accessToken);

        $googleGmail->users_labels
            ->shouldReceive('listUsersLabels')
            ->once()
            ->with('me')
            ->andReturn($listLabelsResponse);

        $this->gmailService->log
            ->shouldReceive('info');

        $this->gmailService->log
            ->shouldReceive('error');

        $this->expectException(MissingGmailLabelsException::class);

        $this->gmailService
            ->shouldReceive('labels')
            ->passthru();

        $this->gmailService->labels($accessToken);
    }

    /**
     * @group CRM
     * @covers ::labels
     *
     * @dataProvider labelsDataProvider
     *
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ListLabelsResponse $listLabelsResponse
     * @throws ReflectionException
     * @throws MissingFolderException
     * @throws MissingGmailLabelsException
     */
    public function testLabelsWithSearch(Gmail $googleGmail, AccessToken $accessToken, ListLabelsResponse $listLabelsResponse)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $search = [self::FIRST_LABEL_NAME];

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($accessToken);

        $googleGmail->users_labels
            ->shouldReceive('listUsersLabels')
            ->once()
            ->with('me')
            ->andReturn($listLabelsResponse);

        $this->gmailService->log
            ->shouldReceive('info');

        $this->gmailService
            ->shouldReceive('labels')
            ->passthru();

        $result = $this->gmailService->labels($accessToken, $search);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);

        $this->assertInstanceOf(Gmail\Label::class, $result[0]);
        $this->assertSame(self::FIRST_LABEL_ID, $result[0]->id);
        $this->assertSame(self::FIRST_LABEL_NAME, $result[0]->name);
    }

    /**
     * @group CRM
     * @covers ::labels
     *
     * @dataProvider labelsDataProvider
     *
     * @param LegacyMockInterface|MockInterface|Gmail $googleGmail
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param ListLabelsResponse $listLabelsResponse
     * @throws ReflectionException
     * @throws MissingFolderException
     * @throws MissingGmailLabelsException
     */
    public function testLabelsWithMissingFolder(Gmail $googleGmail, AccessToken $accessToken, ListLabelsResponse $listLabelsResponse)
    {
        $this->setToPrivateProperty($this->gmailService, 'gmail', $googleGmail);

        $search = ['wrong_search'];

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->once()
            ->with($accessToken);

        $googleGmail->users_labels
            ->shouldReceive('listUsersLabels')
            ->once()
            ->with('me')
            ->andReturn($listLabelsResponse);

        $this->gmailService->log
            ->shouldReceive('info');

        $this->expectException(MissingFolderException::class);

        $this->gmailService
            ->shouldReceive('labels')
            ->passthru();

        $this->gmailService->labels($accessToken, $search);
    }

    /**
     * @group CRM
     * @covers ::setAccessToken
     *
     * @dataProvider setAccessTokenDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param Google_Client $googleClient
     * @throws MissingGapiIdTokenException
     * @throws ReflectionException
     */
    public function testSetAccessToken(AccessToken $accessToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $accessToken
            ->shouldReceive('getScopeAttribute')
            ->once()
            ->andReturn(explode(' ', self::ACCESS_TOKEN_SCOPE));

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->passthru();

        $result = $this->gmailService->setAccessToken($accessToken);

        $gmail = $this->getFromPrivateProperty($this->gmailService, 'gmail');

        $this->assertInstanceOf(Google_Service_Gmail::class, $result);
        $this->assertInstanceOf(Google_Service_Gmail::class, $gmail);

        $this->assertSame($gmail, $result);

        $client = $result->getClient();

        $this->assertInstanceOf(Google_Client::class, $client);

        $this->assertIsArray($client->getAccessToken());
        $this->assertSame($accessToken->access_token, $client->getAccessToken()['access_token']);
        $this->assertSame($accessToken->id_token, $client->getAccessToken()['id_token']);
        $this->assertSame($accessToken->expires_in, $client->getAccessToken()['expires_in']);
        $this->assertSame(strtotime($accessToken->issued_at) * 1000, $client->getAccessToken()['created']);

        $this->assertEquals(explode(' ', self::ACCESS_TOKEN_SCOPE), $client->getScopes());
    }

    /**
     * @group CRM
     * @covers ::setAccessToken
     *
     * @dataProvider setAccessTokenDataProvider
     *
     * @param LegacyMockInterface|MockInterface|AccessToken $accessToken
     * @param Google_Client $googleClient
     * @throws MissingGapiIdTokenException
     */
    public function testSetAccessTokenWithoutAccessToken(AccessToken $accessToken, Google_Client $googleClient)
    {
        $accessToken->access_token = null;

        $this->expectException(MissingGapiAccessTokenException::class);

        $this->gmailService
            ->shouldReceive('setAccessToken')
            ->passthru();

        $this->gmailService->setAccessToken($accessToken);
    }

    /**
     * @group CRM
     * @covers ::setEmailToken
     *
     * @dataProvider setEmailTokenDataProvider
     *
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @param Google_Client $googleClient
     * @throws MissingGapiIdTokenException
     * @throws ReflectionException
     */
    public function testSetEmailToken(EmailToken $emailToken, Google_Client $googleClient)
    {
        $this->googleService
            ->shouldReceive('getClient')
            ->once()
            ->withNoArgs()
            ->andReturn($googleClient);

        $this->gmailService
            ->shouldReceive('setEmailToken')
            ->passthru();

        $result = $this->gmailService->setEmailToken($emailToken);

        $gmail = $this->getFromPrivateProperty($this->gmailService, 'gmail');

        $this->assertInstanceOf(Google_Service_Gmail::class, $result);
        $this->assertInstanceOf(Google_Service_Gmail::class, $gmail);

        $this->assertSame($gmail, $result);

        $client = $result->getClient();

        $this->assertInstanceOf(Google_Client::class, $client);

        $this->assertIsArray($client->getAccessToken());
        $this->assertSame($emailToken->getAccessToken(), $client->getAccessToken()['access_token']);
        $this->assertSame($emailToken->getIdToken(), $client->getAccessToken()['id_token']);
        $this->assertSame($emailToken->getExpiresIn(), $client->getAccessToken()['expires_in']);
        $this->assertSame($emailToken->getIssuedUnix(), $client->getAccessToken()['created']);

        $this->assertEquals([self::ACCESS_TOKEN_SCOPE], $client->getScopes());
    }

    /**
     * @group CRM
     * @covers ::setEmailToken
     *
     * @dataProvider setEmailTokenDataProvider
     *
     * @param LegacyMockInterface|MockInterface|EmailToken $emailToken
     * @param Google_Client $googleClient
     * @throws MissingGapiIdTokenException
     */
    public function testSetEmailTokenWithoutAccessToken(EmailToken $emailToken, Google_Client $googleClient)
    {
        $emailToken->setAccessToken('');

        $this->expectException(MissingGapiAccessTokenException::class);

        $this->gmailService
            ->shouldReceive('setEmailToken')
            ->passthru();

        $this->gmailService->setEmailToken($emailToken);
    }

    /**
     * @return array[]
     */
    public function authDataProvider(): array
    {
        $googleClient = Mockery::mock(Google_Client::class);

        $authCode = self::AUTH_CODE;
        $redirectUrl = self::REDIRECT_URL;

        $accessToken = [
            'access_token' => self::ACCESS_TOKEN_ACCESS_TOKEN,
            'refresh_token' => self::ACCESS_TOKEN_REFRESH_TOKEN,
            'id_token' => self::ACCESS_TOKEN_ID_TOKEN,
            'scope' => self::ACCESS_TOKEN_SCOPE,
            'issued_at' => self::ACCESS_TOKEN_ISSUED_AT,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_IN,
        ];

        return [[$googleClient, $authCode, $redirectUrl, $accessToken]];
    }

    /**
     * @return array[]
     * @throws PropertyDoesNotExists
     * @throws ReflectionException
     */
    public function profileDataProvider(): array
    {
        $emailToken = new EmailToken([
            'access_token' => self::ACCESS_TOKEN_ACCESS_TOKEN,
            'refresh_token' => self::ACCESS_TOKEN_REFRESH_TOKEN,
            'id_token' => self::ACCESS_TOKEN_ID_TOKEN,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_IN,
            'issued_at' => self::ACCESS_TOKEN_ISSUED_AT,
            'scopes' => explode(' ',self::ACCESS_TOKEN_SCOPE),
        ]);

        $googleGmail = Mockery::mock(Gmail::class);
        $users = Mockery::mock(Users::class);

        $this->setToPrivateProperty($googleGmail, 'users', $users);

        $gmailProfile = Mockery::mock(Gmail\Profile::class);

        return [[$emailToken, $googleGmail, $gmailProfile]];
    }

    /**
     * @return array[]
     * @throws PropertyDoesNotExists
     * @throws ReflectionException
     */
    public function sendDataProvider(): array
    {
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->dealer_id = self::ACCESS_TOKEN_DEALER_ID;

        $smtpConfig = new SmtpConfig(['access_token' => $accessToken]);

        $parsedEmail = new ParsedEmail([
            'id' => self::PARSED_EMAIL_ID,
            'message_id' => self::PARSED_MESSAGE_ID,
            'to' => self::PARSED_EMAIL_TO,
            'to_name' => self::PARSED_EMAIL_TO_NAME,
            'from' => self::PARSED_EMAIL_FROM,
            'from_name' => self::PARSED_EMAIL_FROM_NAME,
            'subject' => self::PARSED_EMAIL_SUBJECT,
            'body' => self::PARSED_EMAIL_BODY,
        ]);

        $googleGmail = Mockery::mock(Gmail::class);
        $usersMessages = Mockery::mock(Gmail\Resource\UsersMessages::class);

        $gmailMessage = new Gmail\Message();
        $gmailMessage->id = self::GMAIL_MESSAGE_ID;

        $this->setToPrivateProperty($googleGmail, 'users_messages', $usersMessages);

        return [[$smtpConfig, $parsedEmail, $googleGmail, $gmailMessage]];
    }

    /**
     * @return array[]
     * @throws ReflectionException
     */
    public function messagesDataProvider(): array
    {
        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->dealer_id = self::ACCESS_TOKEN_DEALER_ID;

        $firstGmailMessage = new Gmail\Message();
        $firstGmailMessage->id = self::GMAIL_MESSAGE_ID;

        $secondGmailMessage = new Gmail\Message();
        $secondGmailMessage->id = self::SECOND_GMAIL_MESSAGE_ID;

        $listMessagesResponse = new Gmail\ListMessagesResponse();
        $listMessagesResponse->setMessages([$firstGmailMessage, $secondGmailMessage]);

        $googleGmail = Mockery::mock(Gmail::class);
        $usersMessages = Mockery::mock(Gmail\Resource\UsersMessages::class);

        $this->setToPrivateProperty($googleGmail, 'users_messages', $usersMessages);

        $labels = [
            [
                'id' => self::FIRST_LABEL_ID
            ],
            [
                'id' => self::SECOND_LABEL_ID
            ]
        ];

        return [[$accessToken, $listMessagesResponse, $googleGmail, $labels]];
    }

    /**
     * @return array[]
     * @throws ReflectionException
     */
    public function messageDataProvider(): array
    {
        $mailId = self::MAIL_ID;

        $googleGmail = Mockery::mock(Gmail::class);
        $usersMessages = Mockery::mock(Gmail\Resource\UsersMessages::class);

        $this->setToPrivateProperty($googleGmail, 'users_messages', $usersMessages);

        $messagePartHeaderMessageId = new Gmail\MessagePartHeader();
        $messagePartHeaderMessageId->setName('Message-ID');
        $messagePartHeaderMessageId->setValue(self::MESSAGE_HEADER_MESSAGE_ID);

        $messagePartHeaderSubject = new Gmail\MessagePartHeader();
        $messagePartHeaderSubject->setName('Subject');
        $messagePartHeaderSubject->setValue(self::MESSAGE_HEADER_SUBJECT);

        $messagePartHeaderTo = new Gmail\MessagePartHeader();
        $messagePartHeaderTo->setName('To');
        $messagePartHeaderTo->setValue(self::MESSAGE_HEADER_TO_NAME . '<' . self::MESSAGE_HEADER_TO . '>');

        $messagePartHeaderFrom = new Gmail\MessagePartHeader();
        $messagePartHeaderFrom->setName('From');
        $messagePartHeaderFrom->setValue(self::MESSAGE_HEADER_FROM_NAME . '<' . self::MESSAGE_HEADER_FROM . '>');

        $headers = [
            $messagePartHeaderMessageId,
            $messagePartHeaderSubject,
            $messagePartHeaderTo,
            $messagePartHeaderFrom
        ];

        $messageBody = new Gmail\MessagePartBody();
        $messageBody->setData(self::MESSAGE_BODY_DATA);

        $messagePart = new Gmail\MessagePart();
        $messagePart->setHeaders($headers);
        $messagePart->setBody($messageBody);

        $gmailMessage = new Gmail\Message();
        $gmailMessage->id = self::GMAIL_MESSAGE_ID;
        $gmailMessage->setPayload($messagePart);

        return [[$mailId, $googleGmail, $gmailMessage]];
    }

    /**
     * @return array[]
     * @throws ReflectionException
     */
    public function moveDataProvider(): array
    {
        $mailId = self::MAIL_ID;

        $googleGmail = Mockery::mock(Gmail::class);
        $usersMessages = Mockery::mock(Gmail\Resource\UsersMessages::class);

        $this->setToPrivateProperty($googleGmail, 'users_messages', $usersMessages);

        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->dealer_id = self::ACCESS_TOKEN_DEALER_ID;

        $labels = [
            [
                'id' => self::FIRST_LABEL_ID
            ],
            [
                'id' => self::SECOND_LABEL_ID
            ]
        ];

        $gmailMessage = new Gmail\Message();
        $gmailMessage->id = self::GMAIL_MESSAGE_ID;

        return [[$mailId, $googleGmail, $accessToken, $labels, $gmailMessage]];
    }

    /**
     * @return array[]
     * @throws ReflectionException
     */
    public function labelsDataProvider(): array
    {
        $googleGmail = Mockery::mock(Gmail::class);
        $usersLabels = Mockery::mock(Gmail\Resource\UsersLabels::class);

        $this->setToPrivateProperty($googleGmail, 'users_labels', $usersLabels);

        $accessToken = $this->getEloquentMock(AccessToken::class);
        $accessToken->dealer_id = self::ACCESS_TOKEN_DEALER_ID;

        $firsLabel = new Gmail\Label();
        $firsLabel->id = self::FIRST_LABEL_ID;
        $firsLabel->name = self::FIRST_LABEL_NAME;

        $secondLabel = new Gmail\Label();
        $secondLabel->id = self::SECOND_LABEL_ID;
        $secondLabel->name = self::SECOND_LABEL_NAME;

        $listLabelsResponse = new Gmail\ListLabelsResponse();
        $listLabelsResponse->setLabels([$firsLabel, $secondLabel]);

        return [[$googleGmail, $accessToken, $listLabelsResponse]];
    }

    /**
     * @return array[]
     */
    public function setAccessTokenDataProvider(): array
    {
        $accessToken = $this->getEloquentMock(AccessToken::class);

        $accessToken->dealer_id = self::ACCESS_TOKEN_DEALER_ID;
        $accessToken->access_token = self::ACCESS_TOKEN_ACCESS_TOKEN;
        $accessToken->id_token = self::ACCESS_TOKEN_ID_TOKEN;
        $accessToken->expires_in = self::ACCESS_TOKEN_EXPIRES_IN;
        $accessToken->issued_at = self::ACCESS_TOKEN_ISSUED_AT;

        $googleClient = new Google_Client();

        return [[$accessToken, $googleClient]];
    }

    /**
     * @return array[]
     * @throws PropertyDoesNotExists
     */
    public function setEmailTokenDataProvider(): array
    {
        $emailToken = new EmailToken([
            'access_token' => self::ACCESS_TOKEN_ACCESS_TOKEN,
            'refresh_token' => self::ACCESS_TOKEN_REFRESH_TOKEN,
            'id_token' => self::ACCESS_TOKEN_ID_TOKEN,
            'expires_in' => self::ACCESS_TOKEN_EXPIRES_IN,
            'issued_at' => self::ACCESS_TOKEN_ISSUED_AT,
            'scopes' => explode(' ',self::ACCESS_TOKEN_SCOPE),
        ]);

        $googleClient = new Google_Client();

        return [[$emailToken, $googleClient]];
    }
}
