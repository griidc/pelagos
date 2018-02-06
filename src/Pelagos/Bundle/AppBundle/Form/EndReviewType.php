<?php
namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A form to end the dataset submission review.
 */
class EndReviewType extends abstractType
{
    /**
     * Method to build a symfony form.
     *
     * @param FormBuilderInterface $builder The Symfony form builder.
     * @param array                $options The options to pass in.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datasetUdi', TextType::class, array(
                'label' => 'Enter the dataset',
                'required' => true
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'End Review',
                    'attr' => array('class' => 'submitButton')
            ));
    }
}
