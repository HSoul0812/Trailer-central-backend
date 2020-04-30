<?php


use App\Models\CRM\Leads\Lead;

class InteractionsControllerTest extends TestCase
{

    /**
     * Get Mock Lead TC Model
     *
     * @return PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockLeadTc() {
        // Create Mock Email Helper
        $json = json_decode(
            '{
                "lead":{
                    "identifier":"2971628",
                    "first_name":"David",
                    "last_name":"Conway",
                    "email_address":"david@trailercentral.com",
                    "zip":"54004",
                    "newsletter":"Not Subscribed",
                    "date_submitted":"10\/10\/2019 2:56 PM",
                    "comments":""
             }'
        );
        $lead = Lead::arrayToModel($json, 'leads', 'lead');
        $leadTcMock = $this->getMockBuilder(Lead::class)
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
