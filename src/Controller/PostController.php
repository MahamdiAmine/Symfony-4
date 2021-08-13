<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Services\UploadFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        $context = ['posts' => $posts,];
        return $this->render('post/index.html.twig', $context);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @param UploadFile $uploadFile
     * @return Response
     */
    public function create(Request $request, UploadFile $uploadFile): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $file = $request->files->get('post')['attachment'];
            if ($file) {
                $filename = $uploadFile->uploadFile($file);
                $post->setImage($filename);
            }
            // entity manager
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            return $this->redirect($this->generateUrl('post.index'));
        }
        return $this->render(
            'post/create.html.twig',
            ["form" => $form->createView()]
        );
    }

    /**
     * @Route("/show/{id}", name="show")
     * @param Post $post
     * @return Response
     */
    public function show(Post $post): Response
    {
        $context = ["post" => $post];
        return $this->render('post/postDetail.html.twig', $context);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Post $post
     * @return Response
     */
    public function remove(Post $post): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        $this->addFlash("success", "post was deleted successfully");
        return $this->redirect($this->generateUrl('post.index'));
    }
}
