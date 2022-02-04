<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\TranslatedTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TagType
 * @package App\Form\Type
 */
class TagType extends AbstractType
{
    /**
     * @param FormBuilderInterface|FormBuilderInterface[] $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['required' => true, 'constraints' => [
                new NotBlank(),
                new Length(['min' => 3, 'max' => 150])
            ]])
            ->add('translations', EntityType::class, [
                'class' => TranslatedTag::class,
                'choice_label' => 'name'
            ])
            ->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\Tag'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'App_type';
    }
}
