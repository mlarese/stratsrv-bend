<?php
namespace App\Action;

use App\Base\BaseAction;
use App\Entity\Privacy\PrivacyAttachment;
use App\Resource\InvalidAttachmentException;
use App\Resource\FileNotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Traits\UrlHelpers;

/**
 *
 * @author jeff
 *
 */
class AttachmentsView extends BaseAction
{
    /** @var AttachmentsView $instance */
    private static $instance = null;

    /**
     * @param $container
     *
     * @return AttachmentsView
     */
    public static function getInstance($container) {
        if(self::$instance == null) self::$instance = new  AttachmentsView($container);
        return self::$instance;
    }

    use UrlHelpers;

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::clazz()
     */
    public function clazz()
    {
        return PrivacyAttachment::class;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::baseParams()
     */
    public function baseParams()
    {
        return [];
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::mandatoryFields()
     */
    public function mandatoryFields()
    {
        return [];
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::beforeGetById()
     */
    public function beforeGetById(&$params)
    {

        $fname = $params['fname'];
        $fname = $this->urlB64DecodeString($fname);
        $matches = [];

        if ($fname === false) {
            throw new InvalidAttachmentException();
        }

        $ftype = explode('.', $fname);

        if (! $ftype || empty($ftype)) {
            throw new InvalidAttachmentException();
        }

        $params['foname'] = $fname;

        $params['fname'] = md5($fname);

        $params['ftype'] = $ftype;

        $settings = $this->container->get('settings');

        if (! isset($settings['attachments']) || ! isset($settings['attachments']['users']) || ! isset($settings['attachments']['users']['spf_path'])) {

            throw new \Exception("Attachment path not configured");
        }

        $params['path'] = $settings['attachments']['users']['spf_path'];
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::afterGetById()
     */
    public function afterGetById(&$record, $args)
    {
        if ($record === null) {}
    }

    /**
     *
     * @param array $params
     * @throws FileNotFoundException
     * @return string
     */
    public function findBy($params = [])
    {
        $fname = $params['fname'];

        $ownerId = $this->getOwnerId($this->getRequest());

        $privacyId = $params['uid'];

        $ftype = $params['ftype'][1];

        $filename = sprintf($params['path'], $ownerId, $privacyId) . '/' . $fname ;

        $params['filename'] = $filename;

        // $file = file_get_contents($filename);
        // if ($file === false) throw new FileNotFoundException();

        $fh = fopen($filename, 'rb');

        return  new \Slim\Http\Stream($fh);
    }

    public function generateResponse($file, Request $request, Response $response, $args)
    {


        $response = $response->withAddedHeader('Cache-Control', 'no-cache, must-revalidate');

        $response = $response->withAddedHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
     //
        $response = $response->withHeader('Content-type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=' . $args['foname'])
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            // ->withHeader('Content-Length', filesize($args['fname']))
            ->withBody($file)
            ;

        return $response;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::getById()
     */
    public function getById(Request $request, Response $response, $args)
    {
        try {

            $this->setActionParams($request, $response, $args);
            $this->injectEntityManager();
            $this->beforeGetById($args);
            $uid = $args['uid'];
            $record = $this->findOneBy(["id"=>$uid]);
            // $this->findBy($args);
            $this->afterGetById($record, $args);
            // echo '<pre>'; print_r($args);

            $rs = $this->generateResponse($this->findBy($args), $request, $response, $args);

            return $rs;
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error finding records');
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::get()
     */
    public function get(Request $request, Response $response, $args)
    {
        return $response->withStatus(501, 'Not Implemented');
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::post()
     */
    public function post(Request $request, Response $response, $args)
    {
        return $response->withStatus(501, 'Not Implemented');
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::put()
     */
    public function put(Request $request, Response $response, $args)
    {
        return $response->withStatus(501, 'Not Implemented');
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Base\BaseAction::delete()
     */
    public function delete(Request $request, Response $response, $args)
    {
        return $response->withStatus(501, 'Not Implemented');
    }
}
