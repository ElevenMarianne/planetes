<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\ActivityType;
use App\Repository\ActivityTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/activity-types', name: 'back_activity_types_')]
class ActivityTypeController extends AbstractController
{
    public function __construct(
        private readonly ActivityTypeRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/activity_type/index.html.twig', [
            'activityTypes' => $this->repository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $type = new ActivityType();
        return $this->handleForm($request, $type, false);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, ActivityType $activityType): Response
    {
        return $this->handleForm($request, $activityType, true);
    }

    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(ActivityType $activityType): Response
    {
        $activityType->setIsActive(!$activityType->isActive());
        $this->entityManager->flush();
        $state = $activityType->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Type \"{$activityType->getName()}\" {$state}.");
        return $this->redirectToRoute('back_activity_types_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, ActivityType $activityType): Response
    {
        if ($this->isCsrfTokenValid('delete_at_' . $activityType->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($activityType);
            $this->entityManager->flush();
            $this->addFlash('success', "Type d'activité supprimé.");
        }
        return $this->redirectToRoute('back_activity_types_index');
    }

    private function handleForm(Request $request, ActivityType $type, bool $isEdit): Response
    {
        if ($request->isMethod('POST')) {
            $type->setName($request->request->getString('name'));
            $type->setBasePoints((int) $request->request->get('basePoints', 0));
            $type->setDescription($request->request->getString('description') ?: null);
            $type->setAllowsMultipleParticipants($request->request->getBoolean('allowsMultipleParticipants'));
            $type->setIsActive($request->request->getBoolean('isActive', true));

            if (!$type->getSlug()) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $type->getName()) ?? '');
                $type->setSlug(trim($slug, '-'));
            }

            $errors = $this->validator->validate($type);
            if (count($errors) === 0) {
                $this->entityManager->persist($type);
                $this->entityManager->flush();
                $this->addFlash('success', $isEdit ? "Type mis à jour." : "Type d'activité créé.");
                return $this->redirectToRoute('back_activity_types_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('back/activity_type/form.html.twig', [
            'activityType' => $type,
            'isEdit' => $isEdit,
        ]);
    }
}
