<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
            yield TextField::new('title');
            yield TextField::new('author');
            yield DateTimeField::new('publicationDate');
            yield TextField::new('ISBN');
            yield TextField::new('buyBy');

            yield DateTimeField::new('createdAt')
                ->setRequired(false)
                ->setTimezone('Europe/Paris')
                ->setFormTypeOptions([
                    'html5' => true,
                    'years' => range(date('Y'), date('Y') + 5),
                    'widget' => 'single_text',
                ])->onlyonindex();
            yield ImageField::new('photoFilename')
            ->setUploadDir('public/uploads/photos')
            ->setUploadedFileNamePattern(fn(UploadedFile $file): string => sprintf('photo_%d_%s.%s', 
                time(),
                bin2hex(random_bytes(8)),
                $file->guessExtension()
            ))
            ->setBasePath('uploads/photos')
            ->setLabel('Photo');
        }

        public function configureCrud(Crud $crud): Crud
            {
                return $crud
                    ->setEntityLabelInSingular('book')
                    ->setEntityLabelInPlural('book')
                    ->setSearchFields(['title','author'])
                    ->setDefaultSort(['createdAt' => 'DESC']);
            }
        
        public function configureFilters(Filters $filters): Filters
            {
                return $filters
                    ->add(EntityFilter::new('book'));
            }
    
}
