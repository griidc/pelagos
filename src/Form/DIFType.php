<?php

namespace App\Form;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManager;
use App\Entity\Account;
use App\Entity\DIF;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use App\Util\FundingOrgFilter;

/**
 * A form for creating a DIF.
 */
class DIFType extends AbstractType
{
    /**
     * The entity manager to use in this form type.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The authorization checker to use in this form type.
     *
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * The token storage to use in this form type.
     *
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Utility to filter by funding organization.
     *
     * @var FundingOrgFilter
     */
    private $fundingOrgFilter;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface        $entityManager        The entity manager to use.
     * @param AuthorizationCheckerInterface $authorizationChecker The authorization checker to use.
     * @param TokenStorageInterface         $tokenStorage         The token storage to use.
     * @param FundingOrgFilter              $fundingOrgFilter     Utility to filter by funding organization.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        FundingOrgFilter $fundingOrgFilter
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->fundingOrgFilter = $fundingOrgFilter;
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
            ->add('title', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'Dataset Title (200 Character Maximum)',
                    'rows' => '2',
                    'maxsize' => 200,
                ),
                'label' => 'Dataset Title:',
                'required' => true,
            ))
            ->add('primaryPointOfContact', ChoiceType::class, array(
                'label' => 'Primary Data Point of Contact:',
                'placeholder' => '[PLEASE SELECT PROJECT FIRST]',
                'required' => true,
            ))
            ->add('secondaryPointOfContact', ChoiceType::class, array(
                'label' => 'Additional Data Point of Contact:',
                'placeholder' => '[PLEASE SELECT PROJECT FIRST]',
                'required' => false,
            ))
            ->add('abstract', TextareaType::class, array(
                'attr' => array(
                    'rows' => 6,
                    'placeholder' => 'Please provide a brief narrative describing what, where, why, how, and when the data will be or have been collected or generated (4000 character maximum)',
                    'maxlength' => 4000,
                ),
                'label' => 'Dataset Abstract:',
                'required' => true,
            ))
            ->add('fieldOfStudyEcologicalBiological', CheckboxType::class, array(
                 'label' => 'Ecological/Biological',
                 'required' => false,
            ))
            ->add('fieldOfStudyPhysicalOceanography', CheckboxType::class, array(
                 'label' => 'Physical Oceanography',
                 'required' => false,
            ))
            ->add('fieldOfStudyAtmospheric', CheckboxType::class, array(
                 'label' => 'Atmospheric',
                 'required' => false,
            ))
            ->add('fieldOfStudyChemical', CheckboxType::class, array(
                 'label' => 'Chemical',
                 'required' => false,
            ))
            ->add('fieldOfStudyHumanHealth', CheckboxType::class, array(
                 'label' => 'Human Health',
                 'required' => false,
            ))
            ->add('fieldOfStudySocialCulturalPolitical', CheckboxType::class, array(
                 'label' => 'Social/Cultural/Political',
                 'required' => false,
            ))
            ->add('fieldOfStudyEconomics', CheckboxType::class, array(
                 'label' => 'Economics',
                 'required' => false,
            ))
            ->add('fieldOfStudyOther', TextType::class, array(
                'label' => 'Other Field of Study:',
                'required' => false,
            ))
            ->add('dataSize', ChoiceType::class, array(
                'choices' => array_combine(DIF::DATA_SIZES, DIF::DATA_SIZES),
                'data' => DIF::DATA_SIZES[0],
                'label' => 'Approximate Dataset Size:',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('variablesObserved', TextareaType::class, array(
                'label' => 'Data Parameters and Units:',
                'attr' => array(
                    'rows' => 3,
                    'placeholder' => 'Examples: wind speed (km/hr), salinity (ppt), temperature (°C), PCB concentrations in eggs from a specified species (ng/g wet weight), Ionic Strength (mM)'
                ),
                'required' => false,
            ))
            ->add('collectionMethodFieldSampling', CheckboxType::class, array(
                 'label' => 'Field Sampling',
                 'required' => false,
            ))
            ->add('collectionMethodSimulatedGenerated', CheckboxType::class, array(
                 'label' => 'Simulated/Generated',
                 'required' => false,
            ))
            ->add('collectionMethodLaboratory', CheckboxType::class, array(
                 'label' => 'Laboratory',
                 'required' => false,
            ))
            ->add('collectionMethodLiteratureBased', CheckboxType::class, array(
                 'label' => 'Literature Based',
                 'required' => false,
            ))
            ->add('collectionMethodRemoteSensing', CheckboxType::class, array(
                 'label' => 'Remote Sensing',
                 'required' => false,
            ))
            ->add('collectionMethodOther', TextType::class, array(
                'label' => 'Other Collection Method:',
                'required' => false,
            ))
            ->add('estimatedStartDate', DateType::class, array(
                'label' => 'Start Date:',
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'yyyy-mm-dd'
                ),
            ))
            ->add('estimatedEndDate', DateType::class, array(
                'label' => 'End Date:',
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'yyyy-mm-dd'
                ),
            ))
            ->add('funders', HiddenType::class, array(
                'label' => 'Funders',
                'required' => false,
                'mapped' => false,
            ))
            ->add('additionalFunders', TextType::class, array(
                'label' => 'Additional Funders',
                'required' => false,
            ))
            ->add('spatialExtentDescription', TextareaType::class, array(
                'label' => 'Description:',
                'attr' => array(
                    'placeholder' => 'Example - "lab measurements of oil degradation, no field sampling involved"',
                    'rows' => 5
                ),
                'required' => false,
            ))
            ->add('spatialExtentGeometry', HiddenType::class)
            ->add('nationalDataArchiveNODC', CheckboxType::class, array(
                 'label' => 'National Centers for Environmental Information',
                 'required' => false,
            ))
            ->add('nationalDataArchiveStoret', CheckboxType::class, array(
                 'label' => 'US EPA Storet',
                 'required' => false,
            ))
            ->add('nationalDataArchiveGBIF', CheckboxType::class, array(
                 'label' => 'Global Biodiversity Information Facility',
                 'required' => false,
            ))
            ->add('nationalDataArchiveNCBI', CheckboxType::class, array(
                 'label' => 'National Center for Biotechnology Information',
                 'required' => false,
            ))
            ->add('nationalDataArchiveDataGov', CheckboxType::class, array(
                 'label' => 'Data.gov Dataset Management System',
                 'required' => false,
            ))
            ->add('nationalDataArchiveOther', TextType::class, array(
                'label' => 'Other National Data Archive:',
                'required' => false,
            ))
            ->add('ethicalIssues', ChoiceType::class, array(
                'choices' => array_combine(DIF::ETHICAL_ISSUES, DIF::ETHICAL_ISSUES),
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'placeholder' => false,
            ))
            ->add('ethicalIssuesExplanation', TextType::class, array(
                'label' => 'If yes or uncertain, please explain:',
                'required' => false,
            ))
            ->add('remarks', TextareaType::class, array(
                'attr' => array('rows' => 3),
                'label' => 'Remarks:',
                'required' => false,
            ))
            ->add('issueTrackingTicket', TextType::class, array(
                'label' => 'Issue Tracking Ticket:',
                'required' => false,
            ))
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $event->getForm()
                ->add('primaryPointOfContact', EntityType::class, array(
                    'class' => Person::class,
                ))
                ->add('secondaryPointOfContact', EntityType::class, array(
                    'class' => Person::class,
                ));
            });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $title = $event->getForm()->get('title')->getData();
                $entity = $event->getForm()->getData();
                $entity->setTitle(preg_replace("/(\r|\n)/", " ", $title));
            }
        );
    }

    /**
     * Listener for PRE_SET_DATA event.
     *
     * This adds researchGroup with choices filtered by authorization.
     *
     * @param FormEvent $event The event object for that triggered this listener.
     *
     * @return void
     */
    public function onPreSetData(FormEvent $event)
    {
        $researchGroups = array();
        if ($this->authorizationChecker->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            $researchGroups = $this->entityManager->getRepository(ResearchGroup::class)->findAll();
        } elseif ($this->tokenStorage->getToken()->getUser() instanceof Account) {
            $researchGroups = $this->tokenStorage->getToken()->getUser()->getPerson()->getResearchGroups();
        }

        if ($this->fundingOrgFilter->isActive()) {
            $filterResearchGroupsIds = $this->fundingOrgFilter->getResearchGroupsIdArray();
            $researchGroupsFiltered = array();
            foreach ($researchGroups as $researchGroup) {
                if (in_array($researchGroup->getId(), $filterResearchGroupsIds)) {
                    $researchGroupsFiltered[] = $researchGroup;
                }
            }
            $researchGroups = $researchGroupsFiltered;
        }

        $event->getForm()->add('researchGroup', EntityType::class, array(
            'class' => ResearchGroup::class,
            'choices' => $researchGroups,
            'choice_label' => 'name',
            'placeholder' => '[PLEASE SELECT A PROJECT]',
            'required' => true,
            'label' => 'Project Title:',
            'choice_attr' => function ($choice) {
                return ['locked' => $choice->isLocked() ? 'true' : 'false'];
            },
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
            'data_class' => 'App\Entity\DIF',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ));
    }

    /**
     * Finish the form view.
     *
     * This overrides the empty finishView in AbstractType and removes the POC choices.
     *
     * @param FormView      $view    The view.
     * @param FormInterface $form    The form.
     * @param array         $options The options.
     *
     * @return void
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->children['primaryPointOfContact']->vars['choices'] = array();
        $view->children['secondaryPointOfContact']->vars['choices'] = array();
    }
}
