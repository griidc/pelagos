<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating people.
 */
class PersonType extends AbstractType
{
    /**
     * Proteced router value instance of router service.
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor that provides router.
     *
     * @param RouterInterface $router The router instance.
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

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
            ->add('id', TextType::class, array(
                'label' => 'Person ID:',
                'required' => false,
            ))
            ->add('firstName', TextType::class, array(
                'label' => 'Given Name:',
            ))
            ->add('lastName', TextType::class, array(
                'label' => 'Family Name:',
            ))
            ->add('emailAddress', EmailType::class, array(
                'label' => 'E-Mail Address:',
            ))
            ->add('phoneNumber', TextType::class, array(
                'label' => 'Phone Number:',
                'required' => false,
            ))
            ->add('deliveryPoint', TextareaType::class, array(
                'attr' => array('rows' => 3, 'maxlength' => 300),
                'label' => 'Delivery Point:',
                'required' => false,
            ))
            ->add('city', TextType::class, array(
                'label' => 'City:',
                'required' => false,
            ))
            ->add('administrativeArea', TextType::class, array(
                'label' => 'State/Province:',
                'required' => false,
            ))
            ->add('postalCode', TextType::class, array(
                'label' => 'Postal Code:',
                'required' => false,
            ))
            ->add('country', TextType::class, array(
                'label' => 'Country:',
                'required' => false,
            ))
           ->add('url', TextType::class, array(
                'label' => 'Website:',
                'required' => false,
            ))
            ->add('organization', TextType::class, array(
                'label' => 'Organization:',
                'required' => false,
                'attr' => array(
                    'data-url' => $this->router->generate(
                        'pelagos_api_people_get_distinct_vals',
                        array('property' => 'organization')
                    )
                ),
            ))
            ->add('position', TextType::class, array(
                'label' => 'Position:',
                'required' => false,
                'attr' => array(
                    'data-url' => $this->router->generate(
                        'pelagos_api_people_get_distinct_vals',
                        array('property' => 'position')
                    )
                ),
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
            'data_class' => 'Pelagos\Entity\Person',
            'allow_extra_fields' => true,
        ));
    }
}
