<?php

declare(strict_types=1);

namespace App\Form\Back;

use App\Entity\Astronaut;
use App\Entity\Client;
use App\Entity\Planet;
use App\Enum\Squad;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AstronautType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('client', EntityType::class, [
                'class'        => Client::class,
                'choice_label' => 'name',
                'label'        => 'Client actuel',
                'required'     => false,
                'placeholder'  => '— Aucun client —',
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
            ])
            ->add('hobbies', TextareaType::class, ['label' => 'Hobbies', 'required' => false, 'attr' => ['rows' => 3]])
            ->add('planet', EntityType::class, [
                'class' => Planet::class,
                'choice_label' => 'name',
                'label' => 'Planète',
                'required' => false,
                'placeholder' => '— Aucune planète —',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Arbitre' => 'ROLE_REFEREE',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('arrivedAt', DateType::class, [
                'label'    => "Date d'arrivée",
                'widget'   => 'single_text',
                'required' => false,
                'input'    => 'datetime_immutable',
            ])
            ->add('isActive', null, ['label' => 'Compte actif'])
            ->add('squad', EnumType::class, [
                'class'        => Squad::class,
                'choice_label' => fn(Squad $s) => $s->label(),
                'label'        => 'Squad',
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [new File(['maxSize' => '5M', 'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp']])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Astronaut::class]);
    }
}
