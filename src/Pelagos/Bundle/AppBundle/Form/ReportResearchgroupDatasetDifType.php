<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating the ResearchGroupDatasetDif report for quarterly reporting to the research board.
 */
class ReportResearchgroupDatasetDifType extends AbstractType
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
            ->add('researchGroup', EntityType::class, array(
                'label' => 'Research Group:',
                'class' => 'Pelagos:ResearchGroup',
                'choice_label' => function ($value, $key, $index) {
                    return $value->getName();
                },
                'placeholder' => '[Please Select a Research Group]',
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
            'data_class' => 'Pelagos\Entity\ResearchGroup',
            'allow_extra_fields' => true,
        ));
    }

    /**
     * Finish the form view.
     *
     * This overrides the empty finishView in AbstractType and sorts the dropdown choices.
     *
     * @param FormView      $view    The view.
     * @param FormInterface $form    The form.
     * @param array         $options The options.
     *
     * @return void
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        usort(
            $view->children['researchGroup']->vars['choices'],
            function (ChoiceView $a) {
                return strcasecmp($a->label);
            }
        );
    }
}
