<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class, ['label' => 'Titre de l\'article'])
            ->add('content', TextareaType::class, ['label' => 'Contenu de l\'article'])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'article',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'mimeTypes' => ['image/png','image/jpeg', 'image/webp'],
                        'mimeTypesMessage' => 'Seul les images au format PNG, JPG et WEBP sont accépté.',
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le fichier {{ name }} est trop volumineux.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
