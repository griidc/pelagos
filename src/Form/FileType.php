<?php

namespace App\Form;

use App\Entity\DataCenter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\File;

/**
 * A form for creating Distribution Point.
 */
class FileType extends AbstractType
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
            ->add('fileName', HiddenType::class, array(
                'required' => false,
            ))
            ->add('fileSize', HiddenType::class, array(
                'required' => false,
            ))
            ->add('fileSha256Hash', HiddenType::class, array(
                'required' => false,
            ))
            ->add('uploadedAt', HiddenType::class, array(
                'required' => false,
            ))
            ->add('uploadedBy', HiddenType::class, array(
                'required' => false,
            ))
            ->add('description', HiddenType::class, array(
                'required' => false,
            ))
            ->add('filePath', HiddenType::class, array(
                'required' => false,
            ))
            ->add('status', HiddenType::class, array(
                'required' => false,
            ))
            ;
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
            'data_class' => File::class,
            'allow_extra_fields' => true,
        ));
    }
}
