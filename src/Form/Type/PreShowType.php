<?php
declare(strict_types=1);

namespace App\Form\Type;


use App\Entity\Picture;
use App\Model\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PreShowType
 * @package App\Form\Type
 */
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
                'choice_label' => function(Picture $picture, $key, $index) {
                    /** @var $picture Picture */
                    return $picture->getName() . ' : ' . $picture->getStatusInfo();
                },
                'choice_attr' => function(Picture $picture, $key, $index) {
                    /** @var $picture Picture */
                    $attr = [];
                    $attr['class'] = 'alert alert-' . Status::toBootstrap($picture->getStatus());
                    if ($picture->getStatus() !== Status::OK && $picture->getStatus() !== Status::TEMPORARY) {
                        $attr['disabled'] = 'disabled';
                    } else {
                        $attr['checked'] = 'checked';
                    }

                    return $attr;
                },
                'expanded' => true,
                'multiple' => true
            ])
            ->add('submit', SubmitType::class, ['attr' => ['value' => 'Submit', 'class' => 'btn btn-bg btn-primary']]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'pack' => 'App\Entity\Pack',
            'csrf_protection' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'App_pre_show';
    }
}
