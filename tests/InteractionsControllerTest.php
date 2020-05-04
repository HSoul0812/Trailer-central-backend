<?php


use App\Models\CRM\Leads\Lead;

class InteractionsControllerTest extends TestCase
{
    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new InteractionsController();
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'interactions'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);
    }

    /**
     * Test Interactions Index Works
     */
    public function testIndexActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'index');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test That Send Email Opens Modal
     *
     * @author David A Conway Jr.
     */
    public function testSendEmailActionCanBeAccessed()
    {
        // Get Mock User
        $this->getMockUser();

        // Get Mock Lead TC
        $leadTcMock = $this->getMockLeadTc();

        // Override Services With Mock Services
        $this->controller->getServiceLocator()
            ->setAllowOverride(true)
            ->setService('Leads\Model\LeadTC', $leadTcMock);

        // Validate Send Email Form Returns
        $this->routeMatch->setParam('action', 'send-email');
        $this->routeMatch->setParam('id', '1957339135810');

        // Dispatch Post Data
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // Validate Status Code
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test That Send Email Posts Successfully
     *
     * @author David A Conway Jr.
     */
    public function testSendEmailActionValidPost()
    {
        // Initialize Dummy Post data
        $postData = array(
            'email_to'      => 'david@trailercentral.com',
            'email_from'    => 'noreply@trailercentral.com',
            'email_reply'   => 'jaidyn@jrconway.net',
            'email_subject' => 'Send Email Mock Test',
            'email_body'    => '<p>Some testing for the send email functionality!</p><hr /><p>Doug Meadows Rolling M Trailers <br /><br />512-746-2515</p>',
            'product_id'    => '0'
        );

        // Get Mock User
        $this->getMockUser();

        // Get Mock Lead TC
        $leadTcMock = $this->getMockLeadTc();

        // Create Mock Interaction
        $interactionMock = new Interaction();
        $interactionMock->user_id = 6884;
        $interactionMock->tc_lead_id = 2971628;
        $interactionMock->interaction_type = 'EMAIL';
        $interactionMock->interaction_notes = 'E-Mail Sent: ' . $postData['email_subject'];

        // Create Mock Email Helper
        $emailHelperMock = $this->getMockBuilder('Interactions\Email\Email')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHelperMock->expects($this->once())
            ->method('build');
        $emailHelperMock->expects($this->once())
            ->method('send')
            ->willReturn(true);

        // Create Mock Interactions Table
        $interactionTableMock = $this->getMockBuilder('Interactions\Model\InteractionTable')
            ->disableOriginalConstructor()
            ->getMock();
        $interactionTableMock->expects($this->once())
            ->method('buildInteraction')
            ->willReturn($interactionMock);
        $interactionTableMock->expects($this->once())
            ->method('saveInteraction')
            ->willReturn(999999999);

        // Create Mock Email History Table
        $emailHistoryTableMock = $this->getMockBuilder('EmailBuilder\Model\EmailHistoryTable')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHistoryTableMock->expects($this->once())
            ->method('addLogEmail');

        // Override Services With Mock Services
        $this->controller->getServiceLocator()
            ->setAllowOverride(true)
            ->setService('Leads\Model\LeadTC', $leadTcMock)
            ->setService('Interactions\Email\Email', $emailHelperMock)
            ->setService('Interactions\Model\InteractionTable', $interactionTableMock)
            ->setService('EmailBuilder\Model\EmailHistoryTable', $emailHistoryTableMock);

        // Set POST Data
        $this->request->setMethod("POST")->setPost(new \Zend\Stdlib\Parameters($postData));

        // Validate Send Email Form Returns
        $this->routeMatch->setParam('action', 'send-email');
        $this->routeMatch->setParam('id', '1957339135810');

        // Dispatch Post Data
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // Validate Status Code
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test That Reply Email Opens Modal
     *
     * @author David A Conway Jr.
     */
    public function testReplyEmailActionCanBeAccessed()
    {
        // Get Mock User
        $this->getMockUser();

        // Get Mock Lead TC
        $leadTcMock = $this->getMockLeadTc();

        // Create Mock Interaction
        $interactionMock = new Interaction();
        $interactionMock->interaction_id = 999999999;
        $interactionMock->user_id = 6884;
        $interactionMock->tc_lead_id = 2971628;
        $interactionMock->interaction_type = 'EMAIL';
        $interactionMock->interaction_notes = 'E-Mail Sent: Send Email Mock Test';

        // Create Mock EmailHistory
        $emailHistoryMock = new EmailHistory();
        $emailHistoryMock->parent_id = '<99999999999999999@crm.trailercentral.com>';
        $emailHistoryMock->interaction_id = 999999999;
        $emailHistoryMock->subject = 'Send Email Mock Test';
        $emailHistoryMock->body = '<p>Some testing for the send email functionality!</p><hr /><p>Doug Meadows Rolling M Trailers <br /><br />512-746-2515</p>';
        $emailHistoryMock->from_name = 'John Doe';
        $emailHistoryMock->from_email = 'john.doe146186897280@trailercentral.com';

        // Create Mock Interactions Table
        $interactionTableMock = $this->getMockBuilder('Interactions\Model\InteractionTable')
            ->disableOriginalConstructor()
            ->getMock();
        $interactionTableMock->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interactionMock);

        // Create Mock Email History Table
        $emailHistoryTableMock = $this->getMockBuilder('EmailBuilder\Model\EmailHistoryTable')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHistoryTableMock->expects($this->once())
            ->method('getByInteractionId')
            ->willReturn(array($emailHistoryMock));

        // Override Services With Mock Services
        $this->controller->getServiceLocator()
            ->setAllowOverride(true)
            ->setService('Leads\Model\LeadTC', $leadTcMock)
            ->setService('Interactions\Model\InteractionTable', $interactionTableMock)
            ->setService('EmailBuilder\Model\EmailHistoryTable', $emailHistoryTableMock);

        // Validate Send Email Form Returns
        $this->routeMatch->setParam('action', 'reply-email');
        $this->routeMatch->setParam('id', '144819');

        // Dispatch Post Data
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // Validate Status Code
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test That Send Email Posts Successfully
     *
     * @author David A Conway Jr.
     */
    public function testReplyEmailActionValidPost()
    {
        // Initialize Dummy Post data
        $postData = array(
            'email_to'      => 'david@trailercentral.com',
            'email_from'    => 'noreply@trailercentral.com',
            'email_reply'   => 'jaidyn@jrconway.net',
            'email_subject' => 'Send Email Mock Test',
            'email_body'    => '<p>Some testing for the send email functionality!</p><hr /><p>Doug Meadows Rolling M Trailers <br /><br />512-746-2515</p>',
            'product_id'    => '0'
        );

        // Get Mock User
        $this->getMockUser();

        // Get Mock Lead TC
        $leadTcMock = $this->getMockLeadTc();

        // Create Mock Interaction
        $interactionMock = new Interaction();
        $interactionMock->interaction_id = 999999999;
        $interactionMock->user_id = 6884;
        $interactionMock->tc_lead_id = 2971628;
        $interactionMock->interaction_type = 'EMAIL';
        $interactionMock->interaction_notes = 'E-Mail Sent: Send Email Mock Test';

        // Create Mock EmailHistory
        $emailHistoryMock = new EmailHistory();
        $emailHistoryMock->parent_id = '<99999999999999999@crm.trailercentral.com>';
        $emailHistoryMock->interaction_id = 999999999;
        $emailHistoryMock->subject = 'Send Email Mock Test';
        $emailHistoryMock->body = '<p>Some testing for the send email functionality!</p><hr /><p>Doug Meadows Rolling M Trailers <br /><br />512-746-2515</p>';
        $emailHistoryMock->from_name = 'John Doe';
        $emailHistoryMock->from_email = 'john.doe146186897280@trailercentral.com';

        // Create Mock Email Helper
        $emailHelperMock = $this->getMockBuilder('Interactions\Email\Email')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHelperMock->expects($this->once())
            ->method('build');
        $emailHelperMock->expects($this->once())
            ->method('send')
            ->willReturn(true);

        // Create Mock Interactions Table
        $interactionTableMock = $this->getMockBuilder('Interactions\Model\InteractionTable')
            ->disableOriginalConstructor()
            ->getMock();
        $interactionTableMock->expects($this->once())
            ->method('getInteraction')
            ->willReturn($interactionMock);
        $interactionTableMock->expects($this->once())
            ->method('buildInteraction')
            ->willReturn($interactionMock);
        $interactionTableMock->expects($this->once())
            ->method('saveInteraction')
            ->willReturn(999999999);

        // Create Mock Email History Table
        $emailHistoryTableMock = $this->getMockBuilder('EmailBuilder\Model\EmailHistoryTable')
            ->disableOriginalConstructor()
            ->getMock();
        $emailHistoryTableMock->expects($this->once())
            ->method('getByInteractionId')
            ->willReturn(array($emailHistoryMock));
        $emailHistoryTableMock->expects($this->once())
            ->method('addLogEmail');

        // Override Services With Mock Services
        $this->controller->getServiceLocator()
            ->setAllowOverride(true)
            ->setService('Leads\Model\LeadTC', $leadTcMock)
            ->setService('Interactions\Email\Email', $emailHelperMock)
            ->setService('Interactions\Model\InteractionTable', $interactionTableMock)
            ->setService('EmailBuilder\Model\EmailHistoryTable', $emailHistoryTableMock);

        // Set POST Data
        $this->request->setMethod("POST")->setPost(new \Zend\Stdlib\Parameters($postData));

        // Validate Send Email Form Returns
        $this->routeMatch->setParam('action', 'reply-email');
        $this->routeMatch->setParam('id', '144819');

        // Dispatch Post Data
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // Validate Status Code
        $this->assertEquals(200, $response->getStatusCode());
    }



    ####### Common Mocks #######

    /**
     * Get Base Mock User
     *
     * @return mock ZfcUser\Entity\User
     */
    private function getBaseMockUser() {
        // Create Mock User
        $ZfcUserMock = $this->getMockBuilder('ZfcUser\Entity\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getDealerId', 'getFirstName', 'getLastName',
                'getEmail', 'getEmailSignature', 'isSalesPerson'])
            ->getMock();
        $ZfcUserMock->expects($this->any())
            ->method('getId')
            ->willReturn(6884);
        $ZfcUserMock->expects($this->any())
            ->method('getDealerId')
            ->willReturn(1001);
        $ZfcUserMock->expects($this->any())
            ->method('getFirstName')
            ->willReturn('John');
        $ZfcUserMock->expects($this->any())
            ->method('getLastName')
            ->willReturn('Doe');
        $ZfcUserMock->expects($this->any())
            ->method('getEmail')
            ->willReturn('john.doe146186897280@trailercentral.com');
        $ZfcUserMock->expects($this->any())
            ->method('getEmailSignature')
            ->willReturn('');

        // Return User Mock
        return $ZfcUserMock;
    }

    /**
     * Get Mock User
     *
     * @return void
     */
    private function getMockUser() {
        // Get Base Mock User
        $ZfcUserMock = $this->getBaseMockUser();
        $ZfcUserMock->expects($this->any())
            ->method('isSalesPerson')
            ->willReturn(false);

        // Create Mock User Auth
        $authMock = $this->createMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');
        $authMock->expects($this->any())
            ->method('hasIdentity')
            ->willReturn(true);
        $authMock->expects($this->any())
            ->method('getIdentity')
            ->willReturn($ZfcUserMock);

        // Replace User Auth
        $this->controller->getPluginManager()
            ->setService('zfcUserAuthentication', $authMock);
        $this->controller->getServiceLocator()
            ->setAllowOverride(true)
            ->setService('zfcuser_auth_service', $authMock);
    }

    /**
     * Get Mock Lead TC Model
     *
     * @return void
     */
    private function getMockLeadTc() {
        // Create Mock Email Helper
        $json = json_decode('{"lead":{"identifier":"2971628","first_name":"David","last_name":"Conway","email_address":"david@trailercentral.com","zip":"54004","newsletter":"Not Subscribed","date_submitted":"10\/10\/2019 2:56 PM","comments":""}');
        $lead = LeadTC::arrayToModel($json, 'leads', 'lead');
        $leadTcMock = $this->getMockBuilder('Leads\Model\LeadTC')
            ->disableOriginalConstructor()
            ->getMock();
        $leadTcMock->method('findById')
            ->willReturn($lead);
        $leadTcMock->method('getFirstName')
            ->willReturn('David');
        $leadTcMock->method('getLastName')
            ->willReturn('Conway');
        $leadTcMock->method('getEmail')
            ->willReturn('david@trailercentral.com');
        $leadTcMock->method('saveStatus')
            ->willReturn(true);
        return $leadTcMock;
    }
}
