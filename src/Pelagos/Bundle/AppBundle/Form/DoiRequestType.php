<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating research groups.
 */
class DoiRequestType extends AbstractType
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
            ->add('url', TextType::class, array(
                'label' => 'Digital Object URL:',
            ))
            ->add('responsibleParty', TextType::class, array(
                'label' => 'Digital Object Creator(s):',
            ))
            ->add('title', TextareaType::class, array(
                'label' => 'Digital Object Title:',
            ))
            ->add('publisher', TextType::class, array(
                'label' => 'Digital Object Publisher:',
                'data' => 'Harte Research Institute'
            ))
            ->add('publicationDate', DateType::class, array(
                'label' => 'Digital Object Publication Date:',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
            ));
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
            'data_class' => 'Pelagos\Entity\DOI',
            'allow_extra_fields' => true,
        ));
    }
}
