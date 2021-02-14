<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PictureTagType
 * @package App\Form\Type
 */
class PictureTagType extends AbstractType
{
    /**
     * @param FormBuilderInterface|FormBuilderInterface[] $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tags', EntityTagType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Picture'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'App_picture_tag';
    }
}
