<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\File;
use App\Entity\InformationProduct;
use App\Message\InformationProductFiler;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\TextAlign;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\ChoiceConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use Elastica\Aggregation\Max;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

/**
 * @extends AbstractCrudController<InformationProduct>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class InformationProductCrudController extends AbstractCrudController
{

    use EasyAdminCrudTrait;

    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function createEntity(string $entityFqcn)
    {
        $informationProduct = parent::createEntity($entityFqcn);
        /** @var Account $account */
        $account = $this->getUser();
        $informationProduct->setCreator($account->getPerson());
        $file = new File();
        $informationProduct->setFile($file);

        return $informationProduct;
    }

    public static function getEntityFqcn(): string
    {
        return InformationProduct::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $informationProduct = $this->getContext()?->getEntity()->getInstance();

        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id'),
                TextField::new('title'),
                CollectionField::new('researchGroups'),
                TextField::new('creators'),
                TextField::new('publisher'),
                TextField::new('externalDoi'),
                DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                    ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            ];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                IdField::new('id'),
                TextField::new('title'),
                TextField::new('creators'),
                TextField::new('publisher'),
                TextField::new('externalDoi')->setLabel('External DOI'),
                CollectionField::new('researchGroups'),
                CollectionField::new('funders'),
                Field::new('filePathName'),
                UrlField::new('remoteUri'),
                BooleanField::new('published')
                    ->renderAsSwitch(false),
                BooleanField::new('remoteResource')
                    ->renderAsSwitch(false),
                CollectionField::new('productTypeDescriptors'),
                CollectionField::new('digitalResourceTypeDescriptors'),
                DateField::new('creationTimeStamp')->setLabel('Created At')
                    ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
                TextField::new('creator')->setLabel('Created By'),
                DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                    ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
                TextField::new('modifier')->setLabel('Last Modified By'),
            ];
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            TextField::new('creators'),
            TextField::new('publisher'),
            TextField::new('externalDoi')->setLabel('External DOI'),
            AssociationField::new('researchGroups')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.name', 'ASC');
                })
                ->setRequired(true)
                ->setHelp('Select one or more research groups associated with this information product.')
                ->setColumns(100),
            AssociationField::new('funders')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.name', 'ASC');
                })
                ->setRequired(true)
                ->setHelp('Select one or more funders associated with this information product.')
                ->setColumns(100),
            Field::new('filePathName')
                ->setVirtual(true)
                ->setFormType(FileUploadType::class)
                ->addCssClass('field-image')
                ->addJsFiles(Asset::fromEasyAdminAssetPackage('field-image.js'), Asset::fromEasyAdminAssetPackage('field-file-upload.js'))
                ->setColumns('col-md-7 col-xxl-5')
                ->setTextAlign(TextAlign::CENTER)
                ->setHelp('Upload the file associated with this information product.')
                ->setFormTypeOptions([
                    'upload_dir' => 'var/',
                    'file_constraints' => [new FileConstraint(maxSize: '100m')],
                    'upload_new' => static function (UploadedFile $uploadedFile, string $uploadDir, string $fileName) use ($informationProduct) {
                        $fileSize = $uploadedFile->getSize();
                        $uploadedFile->move($uploadDir, $fileName);
                        $file = $informationProduct?->getFile();
                        if (!$file instanceof File) {
                            $file = new File();
                            $informationProduct?->setFile($file);
                        }
                        $file->setFileSize($fileSize);
                        $file->setFilePathName($fileName);
                        $file->setPhysicalFilePath($uploadDir . $fileName);
                    },
                    'upload_filename' => static function (UploadedFile $uploadedFile): string {
                        return $uploadedFile->getClientOriginalName();
                    },
                ]),
            UrlField::new('remoteUri'),
            BooleanField::new('published')
                ->setHelp('Indicates whether this information product is published or not.')
                ->renderAsSwitch(false),
            BooleanField::new('remoteResource')
                ->setHelp('Indicates whether this information product is a remote resource.')
                ->renderAsSwitch(false),
            AssociationField::new('productTypeDescriptors')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.description', 'ASC');
                })
                ->setRequired(true)
                ->setHelp('Select one or more product type descriptors associated with this information product.')
                ->setColumns(100),
            AssociationField::new('digitalResourceTypeDescriptors')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->orderBy('entity.description', 'ASC');
                })
                ->setRequired(true)
                ->setHelp('Select one or more digital resource type descriptors associated with this information product.')
                ->setColumns(100),
        ];
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('Information Products')
            ->setEntityLabelInSingular('Information Product')
            ->setPageTitle(Crud::PAGE_INDEX, 'Information Products')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Information Product')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Information Product')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Information Product Details')
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus-circle')
                    ->setLabel('Create New Information Product');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('View');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-edit')
                    ->setLabel('Edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (InformationProduct $informationProduct) {
                        return $informationProduct->isDeletable();
                    });
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (InformationProduct $informationProduct) {
                        return $informationProduct->isDeletable();
                    });
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action
                    ->setIcon('fa fa-save')
                    ->setLabel('Save and Close');
            });
    }
}
