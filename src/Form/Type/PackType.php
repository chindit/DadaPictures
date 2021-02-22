<?php

namespace App\Form\Type;

use App\Entity\Pack;
use App\Entity\Tag;
use App\Entity\User;
use App\Model\Languages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

/**
 * Class PackType
 * @package App\Form\Type
 */
class PackType extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

    /**
     * @param FormBuilderInterface|FormBuilderInterface[] $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['required' => true, 'label' => 'pack.form.name'])
            ->add('file', FileType::class, ['label' => 'pack.form.archive'])
            ->add('tags', EntityTagType::class, [
                'class' => Tag::class,
                'choice_label' => function (Tag $tag) {
                    /** @var ?User $user */
                    $user = $this->security->getUser();
                    $language = $user?->getLanguage() ?? Languages::EN;
                    return $tag->getTranslation($language)?->getName();
                },
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => 'pack.form.tags'
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Pack::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'App_pack';
    }
}
