<?php

namespace App\Form;

use App\Entity\FundingCycle;
use App\Entity\ResearchGroup;
use App\Repository\ResearchGroupRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating research groups.
 */
class ResearchGroupType extends AbstractType
{
    /**
     * To hold injected ResearchGroupRepository.
     *
     * @var ResearchGroupRepository
     */
    private ResearchGroupRepository $researchGroupRepository;

    /**
     * A repository for Research Groups.
     *
     * @param ResearchGroupRepository $researchGroupRepository
     */
    public function __construct(ResearchGroupRepository $researchGroupRepository)
    {
        $this->researchGroupRepository = $researchGroupRepository;
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
        $nextId = $this->researchGroupRepository->getNextAvailableId(ResearchGroup::MIN_ID, ResearchGroup::MAX_ID);
        $builder
            ->add('id', TextType::class, array(
                'label' => 'Research Group ID:',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Next Available ID: ' . $nextId,
                    'next-id' => $nextId,
                ),
            ))
            ->add('name', TextType::class, array(
                'label' => 'Name:',
            ))
            ->add('shortName', TextType::class, array(
                'label' => 'Short Name:',
                'required' => true
            ))
            ->add('fundingCycle', EntityType::class, array(
                'label' => 'Funding Cycle',
                'class' => FundingCycle::class,
                'choice_label' => 'name',
            ))
            ->add('url', TextType::class, array(
                'label' => 'Website',
                'required' => false,
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
            ->add('description', TextareaType::class, array(
                'label' => 'Description:',
                'attr' => array('rows' => 5),
                'required' => false,
            ))
            ->add('emailAddress', TextType::class, array(
                'label' => 'E-Mail Address:',
                'required' => false,
            ))
            ->add('locked', ChoiceType::class, array(
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'placeholder' => '[PLEASE SELECT A STATE]',
                'label' => 'Closed Out:',
                'required' => true,
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
            'data_class' => 'App\Entity\ResearchGroup',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ));
    }
}
