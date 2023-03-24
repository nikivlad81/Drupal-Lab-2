<?php

namespace Drupal\my_queue\Mail;

use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\TranslationManager;

/**
 * Handles the assembly and dispatch of HTML emails.
 */
final class MailHandler {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The language default.
   *
   * @var \Drupal\Core\Language\LanguageDefault
   */
  protected LanguageDefault $languageDefault;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  private TranslationInterface $stringTranslation;

  /**
   * Constructs a new MailHandler object.
   *
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Language\LanguageDefault $language_default
   *   The language default.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager, LanguageDefault $language_default, TranslationInterface $string_translation) {
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
    $this->languageDefault = $language_default;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Composes and send email message.
   *
   * @param string $to
   *   The email address or addresses where the message will be sent to.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $subject
   *   The message subject. To be properly translated with body, it must be
   *   TranslatableMarkup when we switch language.
   * @param array $body
   *   A render array representing message body.
   * @param array $params
   *   Parameters to build the email.
   *
   * @return bool
   *   TRUE if the email was sent successfully, FALSE otherwise.
   *
   * @see \Drupal\Core\Mail\MailManagerInterface::mail()
   */
  public function sendMail(string $to, TranslatableMarkup $subject, array $body, array $params = []): bool {
    $default_params = [
      'headers' => [
        'Content-Type' => 'text/html; charset=UTF-8;',
        'Content-Transfer-Encoding' => '8Bit',
      ],
      'id' => 'mail',
      'reply-to' => NULL,
      'subject' => $subject,
      'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
      'body' => $body,
    ];
    if (!empty($params['cc'])) {
      $default_params['headers']['Cc'] = $params['cc'];
    }
    if (!empty($params['bcc'])) {
      $default_params['headers']['Bcc'] = $params['bcc'];
    }
    $params = array_replace($default_params, $params);

    if ($params['langcode'] != $default_params['langcode']) {
      $this->changeActiveLanguage($params['langcode']);
    }

    $message = $this->mailManager->mail('my_queue', $params['id'], $to, $params['langcode'], $params, $params['reply-to']);

    if ($params['langcode'] != $default_params['langcode']) {
      $this->changeActiveLanguage($default_params['langcode']);
    }

    return (bool) $message['result'];
  }

  /**
   * Changes the active language for translations.
   *
   * @param string $langcode
   *   The langcode.
   */
  protected function changeActiveLanguage($langcode): void {
    if (!$this->languageManager->isMultilingual()) {
      return;
    }
    $language = $this->languageManager->getLanguage($langcode);
    if (!$language) {
      return;
    }
    $this->languageDefault->set($language);
    $this->languageManager->setConfigOverrideLanguage($language);
    $this->languageManager->reset();

    if ($this->stringTranslation instanceof TranslationManager) {
      $this->stringTranslation->setDefaultLangcode($language->getId());
      $this->stringTranslation->reset();
    }
  }

}
