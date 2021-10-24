<?php

namespace App\Form;

use App\Entity\Article;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('tags',TextType::class,[
                'required' => false,
                'mapped' => false
            ])
            ->add('date')
            ->add('description',CKEditorType::class)
            ->add('thumbnail', FileType::class, [

                'label' => 'Choisir une image :  ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '8096k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PNG OR JPG document',
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
