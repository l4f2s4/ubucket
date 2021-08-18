<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TreeGraphController extends AbstractController
{
    /**
     * @Route("/tree/graph", name="tree_graphs")
     */
    public function index()
    {

        $parametersToValidate = $request->query->all();
          $parametersToValidate=array_values($parametersToValidate);
          $name = $parametersToValidate[1];
          $repo = $this->repoRepository->findOneBy(['name' => $name]);
          $gitService = $this->gitservice->create($repo);

          $repoObject = $this->appdataobjectFactory->create($repo);
          $graphItems = $repoObject->getGraph();

        return $this->render('tree_graph/index.html.twig', [
            'repo' => $repo,
            'graphItems' => $graphItems,
        ]);
    }
}
