<?php

declare(strict_types=1);

namespace App\Controller\Back;

use App\Entity\Grade;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/back/grades', name: 'back_grades_')]
class GradeController extends AbstractController
{
    public function __construct(
        private readonly GradeRepository $repository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('back/grade/index.html.twig', [
            'grades' => $this->repository->findBy([], ['minPoints' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request): Response
    {
        $grade = new Grade();
        return $this->handleForm($request, $grade, false);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Grade $grade): Response
    {
        return $this->handleForm($request, $grade, true);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Grade $grade): Response
    {
        if ($this->isCsrfTokenValid('delete_grade_' . $grade->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($grade);
            $this->entityManager->flush();
            $this->addFlash('success', "Grade \"{$grade->getName()}\" supprimé.");
        }
        return $this->redirectToRoute('back_grades_index');
    }

    private function handleForm(Request $request, Grade $grade, bool $isEdit): Response
    {
        if ($request->isMethod('POST')) {
            $grade->setName($request->request->getString('name'));
            $grade->setMinPoints((int) $request->request->get('minPoints', 0));
            $grade->setSortOrder((int) $request->request->get('sortOrder', 0));
            $grade->setIcon($request->request->getString('icon') ?: null);

            // Auto-générer le slug depuis le nom si absent
            if (!$grade->getSlug()) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $grade->getName()) ?? '');
                $grade->setSlug(trim($slug, '-'));
            }

            $errors = $this->validator->validate($grade);
            if (count($errors) === 0) {
                $this->entityManager->persist($grade);
                $this->entityManager->flush();
                $this->addFlash('success', $isEdit ? "Grade mis à jour." : "Grade créé.");
                return $this->redirectToRoute('back_grades_index');
            }

            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('back/grade/form.html.twig', [
            'grade' => $grade,
            'isEdit' => $isEdit,
        ]);
    }
}
