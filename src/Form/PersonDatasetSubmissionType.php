<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Person;
use App\Entity\PersonDatasetSubmission;

/**
 * A form for creating Person to Dataset Submission links.
 *
 * @extends AbstractType<PersonDatasetSubmission>
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
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', PersonType::class)
            ->add('role', ChoiceType::class, array(
                'label' => 'Role',
                'choices' => PersonDatasetSubmission::getRoleChoices(),
                'placeholder' => '[Please select a role.]',
            ))
            ->add('primaryContact', CheckboxType::class, array(
                'label' => 'Is Primary Contact',
                'required' => false,
                'attr' => array('style' => 'display:none;'),
            ))
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $event->getForm()->add('person', EntityType::class, array(
                    'label' => 'Person',
                    'class' => Person::class,
                    'choice_label' => function (Person $value) {
                        return $value->getLastName() . ', ' . $value->getFirstName() . ', ' . $value->getEmailAddress();
                    },
                    'placeholder' => '[Please select a person.]',
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
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PersonDatasetSubmission::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ));
    }
}
