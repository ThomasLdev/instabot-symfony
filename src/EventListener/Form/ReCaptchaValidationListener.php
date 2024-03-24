<?php

/**
 * @author ThomasLdev
 */

declare(strict_types=1);

namespace App\EventListener\Form;

use ReCaptcha\ReCaptcha;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

class ReCaptchaValidationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ReCaptcha $reCaptcha
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $request = Request::createFromGlobals();
        $captchaResponse = $request->request->get('g-recaptcha-response');
        $invalidError = new FormError('The captcha is invalid. Please try again.');

        if (false === is_string($captchaResponse)) {
            $event->getForm()->addError($invalidError);

            return;
        }

        $result = $this->reCaptcha
            ->setExpectedHostname($request->getHost())
            ->verify($captchaResponse, $request->getClientIp());

        if (!$result->isSuccess()) {
            $event->getForm()->addError($invalidError);
        }
    }
}
