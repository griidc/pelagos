<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Pelagos\Entity\Person;
use Pelagos\Entity\PersonDatasetSubmission;

/**
 * A form for creating Person to Dataset Submission links.
 */
class PersonDatasetSubmissionType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', PersonType::class)
            ->add('role', ChoiceType::class, array(
                'label' => 'Role',
                'choices' => PersonDatasetSubmission::getRoleChoices(),
                'placeholder' => '[Please Select a Role]',
            ))
            ->add('primaryContact', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $event->getForm()->add('person', EntityType::class, array(
                    'label' => 'Person',
                    'class' => Person::class,
                    'choice_label' => function ($value, $key, $index) {
                        return $value->getLastName() . ', ' . $value->getFirstName() . ', ' . $value->getEmailAddress();
                    },
                    'placeholder' => '[Please Select a Person]',
                ));
            });
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PersonDatasetSubmission::class,
            'allow_extra_fields' => true,
        ));
    }
}
