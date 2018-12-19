<?php

namespace Pumukit\Up2u\PR\WebTVBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Pagerfanta\Pagerfanta;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\CoreBundle\Controller\AdminController;

class MyVideosController extends Controller implements AdminController
{
    private function createPager($objects, $page, $limit = 10)
    {
        $adapter = new DoctrineODMMongoDBAdapter($objects);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);
        foreach($pagerfanta as $mmobj){
            //TODO: Review filters. Without this, the WebTVBundle filter is applied, even if it's not enabled
        }
        return $pagerfanta;
    }

    private function updateBreadcrumbs($title, $routeName, array $routeParameters = array())
    {
        $breadcrumbs = $this->get('pumukit_web_tv.breadcrumbs');
        $breadcrumbs->add($title, $routeName, $routeParameters);
    }

    /**
     * To extends this controller.
     */
    protected function getParameters()
    {
        return array(
            $this->container->getParameter('scroll_list_byuser'),
            $this->container->getParameter('columns_objs_byuser'),
            $this->container->getParameter('limit_objs_byuser'),
            $this->container->getParameter('pumukitschema.personal_scope_role_code'),
        );
    }

    //* @Route("/admin/myvideos", name="pumukit_poddium_myvideos_index", defaults={"filter": false})
    /**
     * @Route("/admin/myvideos", name="pumukit_poddium_myvideos_index")
     * @Template("PumukitWebTVBundle:MyVideos:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        list($scroll_list, $numberCols, $limit, $roleCode) = $this->getParameters();
        $user = $this->getUser();
        $fullname = $user->getFullname();
        $title = $this->get('translator')->trans('Videos of %fullname%', array('%fullname%' => $fullname));
        $this->updateBreadcrumbs($title, 'pumukit_poddium_myvideos_index');

        $person = $user->getPerson();

        $repo = $this->get('doctrine_mongodb')->getRepository('PumukitSchemaBundle:MultimediaObject');
        //$mmobjs = $repo->createBuilderByPersonIdWithRoleCod($person->getId(), $roleCode, array('public_date' => -1));
        //$mmobjs->field('status')->notEqual(MultimediaObject::STATUS_PROTOTYPE)->field('islive')->equals(false);
        $mmobjs = $repo->createStandardQueryBuilder();
        $pagerfanta = $this->createPager($mmobjs, $request->query->get('page', 1), $limit);


        return array(
            'title' => $title,
            'objects' => $pagerfanta,
            'user' => $user,
            'scroll_list' => $scroll_list,
            'number_cols' => $numberCols,
            'type' => 'multimediaobject',
        );
    }
}
