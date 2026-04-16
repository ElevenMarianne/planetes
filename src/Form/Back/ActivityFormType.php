<?php

declare(strict_types=1);

namespace App\Form\Back;

use App\Entity\Activity;
use App\Entity\ActivityType;
use App\Entity\Astronaut;
use App\Entity\Planet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EntityType::class, [
                'class' => ActivityType::class,
                'choice_label' => fn (ActivityType $t) => $t->getName() . ' (' . $t->getBasePoints() . ' pts)',
                'label' => 'Type d\'activité',
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('at')
                    ->where('at.isActive = true')
                    ->orderBy('at.name', 'ASC'),
            ])
            ->add('astronauts', EntityType::class, [
                'class' => Astronaut::class,
                'choice_label' => fn (Astronaut $a) => $a->getFullName(),
                'label' => 'Astronaute(s)',
                'multiple' => true,
                'expanded' => false,
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('a')
                    ->where('a.isActive = true')
                    ->orderBy('a.lastName', 'ASC'),
                'attr' => ['class' => 'astronaut-select-hidden'],
            ])
            ->add('planet', EntityType::class, [
                'class'        => Planet::class,
                'choice_label' => fn (Planet $p) => $p->getName(),
                'label'        => 'Planète',
                'required'     => false,
                'placeholder'  => '— sélectionner une planète —',
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('p')->orderBy('p.name', 'ASC'),
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Note (optionnelle)',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Contexte, lien, détail…'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Activity::class]);
    }
}
