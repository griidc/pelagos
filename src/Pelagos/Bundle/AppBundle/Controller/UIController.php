<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * The default controller for the Pelagos App Bundle.
 */
class UIController extends Controller
{
    /**
     * The index action.
     *
     * @param string $template The template name.
     * @param string $id       The id of the entity to retrieve.
     *
     * @return Response A Response instance.
     */
    public function indexAction($template, $id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        
        $ui = array();

        $ui['ui']['templateName'] = "$template.html.twig";

        if (isset($id)) {
            $ui[$template] = $entityHandler->get($template, $id);
        } else {
            $ui[$template] = new \Pelagos\Entity\ResearchGroup;
        }

        $ui['can_edit'] = (true) ? 'true' : 'false';

        $ui['entityService'] = $entityHandler;

        return $this->render('PelagosAppBundle:template:UI.html.twig', $ui);
    }
}
