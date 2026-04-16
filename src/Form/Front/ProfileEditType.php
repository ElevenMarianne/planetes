<?php

declare(strict_types=1);

namespace App\Form\Front;

use App\Entity\Astronaut;
use App\Entity\Client;
use App\Enum\Squad;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('client', EntityType::class, [
                'class'        => Client::class,
                'choice_label' => 'name',
                'label'        => 'Client / Projet actuel',
                'required'     => false,
                'placeholder'  => '— Aucun client —',
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
            ])
            ->add('hobbies', TextareaType::class, ['label' => 'Hobbies / Bio', 'required' => false, 'attr' => ['rows' => 4]])
            ->add('squad', EnumType::class, [
                'class'        => Squad::class,
                'choice_label' => fn(Squad $s) => $s->label(),
                'label'        => 'Squad',
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image JPEG, PNG ou WebP.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Astronaut::class]);
    }
}
