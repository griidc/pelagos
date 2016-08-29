<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form for retrieving a count of entities.
 */
class EntityCountType extends EntityType
{
    /**
     * This form type is used to *count* a collection.
     */
    const ACTION = 'count';

    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $collectionName = $options['label'];
        $entityName = $options['data_class']::FRIENDLY_NAME;
        $builder
            ->add('someProperty', TextType::class, array(
                'required' => false,
                'description' => $this->getPropertyFilterDescription($collectionName, $entityName),
            ));
    }
}
