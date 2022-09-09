<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Manager\CronJobManager;
use whatwedo\CronBundle\Manager\ExecutionManager;
use whatwedo\CronBundle\Model\CronJobActivable;
use whatwedo\CronBundle\Repository\ExecutionRepository;

class CronJobController extends AbstractController
{
    public function __construct(
        private CronJobManager $cronJobManager,
        private ExecutionManager $executionManager,
        private ExecutionRepository $executionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function index(): Response
    {
        $cronjobs = $this->cronJobManager->getCronJobs();

        $list = [];
        foreach ($cronjobs as $key => $value) {
            $lastExecution = $this->executionRepository->findLastExecution($value);
            $list[$key]['cronjob'] = $value;
            $list[$key]['lastExecution'] = $lastExecution;
        }

        return $this->render('@whatwedoCron/index.html.twig', [
            'content_title' => 'menu.crud.cronjobs',
            'list' => $list,
        ]);
    }

    public function show(string $class): Response
    {
        $cronJob = $this->cronJobManager->getCronJob($class);
        $lastExecutions = $this->executionRepository->findByJob($cronJob);

        $allowedToRun = true;
        foreach ($lastExecutions as $execution) {
            if ($execution->getState() === Execution::STATE_PENDING) {
                $allowedToRun = false;
                break;
            }
        }

        return $this->render('@whatwedoCron/show.html.twig', [
            'content_title' => $cronJob->getCommand(),
            'cronjob' => $cronJob,
            'lastExecutions' => $lastExecutions,
            'activable' => $cronJob instanceof CronJobActivable,
            'allwedToRun' => $allowedToRun,
            'nextExecutionDate' => $this->executionManager->getNextExecutionDate($cronJob),
        ]);
    }

    public function execution(string $id): Response
    {
        return $this->render('@whatwedoCron/execution.html.twig', [
            'content_title' => 'menu.crud.cronjobs.executions',
            'execution' => $this->executionRepository->find($id),
        ]);
    }

    public function run(string $class): Response
    {
        $cronJob = $this->cronJobManager->getCronJob($class);

        $lastExecutions = $this->executionRepository->findByJob($cronJob);

        $allowedToRun = true;
        foreach ($lastExecutions as $execution) {
            if ($execution->getState() === Execution::STATE_PENDING) {
                $allowedToRun = false;
                break;
            }
        }

        if ($allowedToRun) {
            $execution = new Execution();
            $execution
                ->setJob($class)
                ->setCronJob($cronJob)
                ->setState(Execution::STATE_PENDING);

            $this->entityManager->persist($execution);
            $this->entityManager->flush();

            $this->addFlash('success', 'cronjob.pending');
        }

        return $this->redirect($this->generateUrl('whatwedo_cronjob_show', [
            'class' => $class,
        ]));
    }

    public function activate(string $class): Response
    {
        $cronJob = $this->cronJobManager->getCronJob($class);
        if (! $cronJob instanceof CronJobActivable) {
            throw new \Exception('Job not deactivable in Frontend');
        }

        $this->addFlash('success', 'cronjob.activate');

        $url = $this->generateUrl('whatwedo_cronjob_show', [
            'class' => $class,
        ]);

        return $this->redirect($url);
    }

    public function deactivate(string $class): Response
    {
        $cronJob = $this->cronJobManager->getCronJob($class);

        if (! $cronJob instanceof CronJobActivable) {
            throw new \Exception('Job not deactivable in Frontend');
        }

        $this->addFlash('success', 'cronjob.deactivate');

        $url = $this->generateUrl('whatwedo_cronjob_show', [
            'class' => $class,
        ]);

        return $this->redirect($url);
    }

    public function clean(string $class, string $state): Response
    {
        $cronJob = $this->cronJobManager->getCronJob($class);

        $this->executionRepository->deleteExecutions($cronJob, $state);

        $this->addFlash('success', 'cronjob.cleaned');

        $url = $this->generateUrl('whatwedo_cronjob_show', [
            'class' => $class,
        ]);

        return $this->redirect($url);
    }
}
