<?php
/**
 * Created by PhpStorm.
 * User: ppondicherry
 * Date: 1/25/18
 * Time: 4:26 PM
 */

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