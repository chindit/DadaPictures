<?php
declare(strict_types=1);

namespace AppBundle\Form;


use AppBundle\Entity\Picture;
use AppBundle\Model\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreShowType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('files', EntityType::class, [
                'class' => Picture::class,
                'choices' => $options['pack']->getPictures(),
                'choice_label' => function($picture, $key, $index) {
                    /** @var $picture Picture */
                    return $picture->getName() . ' : ' . $picture->getStatusInfo();
                },
                'choice_attr' => function($picture, $key, $index) {
                    return ['class' => 'alert alert-' . Status::toBootstrap($picture->getStatus())];
                },
                'attr' => ['class' => 'salut'],
                'expanded' => true,
                'multiple' => true
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'pack' => 'AppBundle\Entity\Pack'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_pre_show';
    }
}