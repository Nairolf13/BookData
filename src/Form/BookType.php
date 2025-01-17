<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a title',
                    ]),
                ],
            ])
            ->add('author', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an author',
                    ]),
                ],
            ])
            ->add('publicationDate', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('ISBN', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an ISBN',
                    ]),
                ],
            ])
            ->add('buyBy', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter who bought the book',
                    ]),
                ],
            ])
            ->add('photoFileName', FileType::class, [
                'label' => 'Book Cover (Image file)',
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}
