<?php

namespace Pumukit\Up2u\PR\WebTVBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pumukit\CoreBundle\Controller\PersonalController;
use Pumukit\CoreBundle\Controller\AdminController;

class MyMultimediaObjectController extends Controller implements PersonalController
{
    /**
     * @Route("/admin/video/{id}", name="pumukit_admin_multimediaobject_index" )
     * @Template("PumukitWebTVBundle:MultimediaObject:index.html.twig")
     */
    public function indexAction(MultimediaObject $multimediaObject, Request $request)
    {
        $mmobjService = $this->get('pumukitschema.multimedia_object');
        if ($mmobjService->isPublished($multimediaObject, 'PUCHWEBTV')) {
            if ($mmobjService->hasPlayableResource($multimediaObject) && $multimediaObject->isPublicEmbeddedBroadcast()) {
                return $this->redirect($this->generateUrl('pumukit_webtv_multimediaobject_index', array('id' => $multimediaObject->getId())));
            }
        }

        $track = null;

        if ($request->query->has('track_id')) {
            $track = $multimediaObject->getTrackById($request->query->get('track_id'));

            if (!$track) {
                throw $this->createNotFoundException();
            }

            if ($track->containsTag('download')) {
                $url = $track->getUrl();
                $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?').'forcedl=1';

                return $this->redirect($url);
            }
        }

        $breadcrumbs = $this->get('pumukit_web_tv.breadcrumbs');
        $breadcrumbs->addMultimediaObject($multimediaObject);

        $editorChapters = $this->getChapterMarks($multimediaObject);

        return array(
            'autostart' => $request->query->get('autostart', 'true'),
            'intro' => $this->get('pumukit_baseplayer.intro')->getIntroForMultimediaObject($request->query->get('intro'), $multimediaObject->getProperty('intro')),
            'multimediaObject' => $multimediaObject,
            'track' => $track,
            'editor_chapters' => $editorChapters,
            'cinema_mode' => $this->getParameter('pumukit_web_tv.cinema_mode'),
        );
    }

    protected function getChapterMarks(MultimediaObject $multimediaObject)
    {
        //Get editor chapters for the editor template.
        //Once the chapter marks player plugin is created, this part won't be needed.
        $marks = $this->get('doctrine_mongodb.odm.document_manager')
                      ->getRepository('PumukitSchemaBundle:Annotation')
                      ->createQueryBuilder()
                      ->field('type')->equals('paella/marks')
                      ->field('multimediaObject')->equals(new \MongoId($multimediaObject->getId()))
                      ->getQuery()->getSingleResult();

        $trimming = $this->get('doctrine_mongodb.odm.document_manager')
                      ->getRepository('PumukitSchemaBundle:Annotation')
                      ->createQueryBuilder()
                      ->field('type')->equals('paella/trimming')
                      ->field('multimediaObject')->equals(new \MongoId($multimediaObject->getId()))
                      ->getQuery()->getSingleResult();

        $editorChapters = array();

        if ($marks) {
            $marks = json_decode($marks->getValue(), true);
            if ($trimming) {
                $trimming = json_decode($trimming->getValue(), true);
                if (isset($trimming['trimming'])) {
                    $trimming = $trimming['trimming'];
                }

                foreach ($marks['marks'] as $chapt) {
                    $time = $chapt['s'];
                    if ($trimming['start'] <= $time && $trimming['end'] >= $time) {
                        $editorChapters[] = array(
                            'title' => $chapt['name'],
                            'real_time' => $time,
                            'time_to_show' => $time - $trimming['start'],
                        );
                    }
                }
            }

            usort($editorChapters, function ($a, $b) {
                return $a['real_time'] > $b['real_time'];
            });
        }

        return $editorChapters;
    }
}
