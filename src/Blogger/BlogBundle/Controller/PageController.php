<?php
// src/Blogger/BlogBundle/Controller/PageController.php

namespace Blogger\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
// Import new namespaces
use Blogger\BlogBundle\Entity\Inquiry;
use Blogger\BlogBundle\Form\InquiryType;
use Symfony\Component\HttpFoundation\Request;

class PageController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()
                   ->getEntityManager();

        $blogs = $em->getRepository('BloggerBlogBundle:Blog')
                    ->getLatestBlogs();

        return $this->render('BloggerBlogBundle:Page:index.html.twig', array(
            'blogs' => $blogs
        ));
    }

    public function aboutAction()
    {
        return $this->render('BloggerBlogBundle:Page:about.html.twig');
    }

    public function contactAction(Request $request)
	{
	    $inquiry = new Inquiry();
	    $form = $this->createForm(new InquiryType(), $inquiry);

	    if ($request->getMethod() == 'POST') {
	        $form->submit($request);

	        if ($form->isValid()) {

		        $message = \Swift_Message::newInstance()
		            ->setSubject('Contact enquiry from symblog')
		            ->setFrom('enquiries@symblog.co.uk')
		            ->setTo('email@email.com')
		            ->setBody($this->renderView('BloggerBlogBundle:Page:contactEmail.html.twig', array('inquiry' => $inquiry)));
		        $this->get('mailer')->send($message);

		        $this->get('session')->getFlashBag()->add('blogger-notice', 'Your contact enquiry was successfully sent. Thank you!');

		        // Redirect - This is important to prevent users re-posting
		        // the form if they refresh the page
		        return $this->redirect($this->generateUrl('BloggerBlogBundle_contact'));
		    }
	    }

	    return $this->render('BloggerBlogBundle:Page:contact.html.twig', array(
	        'form' => $form->createView()
	    ));
	}

	public function sidebarAction()
	{
		$em = $this->getDoctrine()
		           ->getEntityManager();

		$tags = $em->getRepository('BloggerBlogBundle:Blog')
		           ->getTags();

		$tagWeights = $em->getRepository('BloggerBlogBundle:Blog')
		                 ->getTagWeights($tags);

		$commentLimit   = $this->container
                           ->getParameter('blogger_blog.comments.latest_comment_limit');
	    $latestComments = $em->getRepository('BloggerBlogBundle:Comment')
	                         ->getLatestComments($commentLimit);

	    return $this->render('BloggerBlogBundle:Page:sidebar.html.twig', array(
	        'latestComments'    => $latestComments,
	        'tags'              => $tagWeights
	    ));
	}
}