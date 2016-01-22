<?php

namespace Pelagos\Bundle\AppBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Form\FormTypeInterface;

/**
 * An exception to be thrown when a form does not validate.
 *
 * @see BadRequestHttpException
 */
class InvalidFormException extends BadRequestHttpException
{
    /**
     * The form that did not validate.
     *
     * @var FormTypeInterface
     */
    protected $form;

    /**
     * Constructor that saves the form in $this->form.
     *
     * @param string            $message A message regarding why the form did not validate.
     * @param FormTypeInterface $form    The form that did not validate.
     *
     * @return void
     */
    public function __construct($message, FormTypeInterface $form = null)
    {
        parent::__construct($message);
        $this->form = $form;
    }

    /**
     * Get the form that did not validate.
     *
     * @return FormTypeInterface|null
     */
    public function getForm()
    {
        return $this->form;
    }
}
