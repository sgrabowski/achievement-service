<?php

namespace App\Tests\Controller;

use App\AchievementBundle\Handler\progress;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class EventControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $container = $this->getContainer();
        $container->get("test.Symfony\Component\Cache\Adapter\TagAwareAdapterInterface")->clear();
    }

    public function testPostEventSuccessful()
    {
        $client = $this->makeClient();

        $content = <<<EOF
        {
            "tag": "player-killed",
            "userId": 1,
            "payload": {
                "killedPlayedId": 12,
                "killedPlayerName": "noob",
                "time": "2012-12-12 12:12:12"
            }     
        }
EOF;
        $client->request("POST", "/events", [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(200, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();
        $this->assertJson($responseBody);
        $update = json_decode($responseBody, true);

        $this->assertArrayHasKey("updatedAchievements", $update);
        $this->assertArrayHasKey("unhandledEvents", $update);

        $this->assertEmpty($update["unhandledEvents"]);

        $updatedAchievements = $update["updatedAchievements"];
        $this->assertInternalType("array", $updatedAchievements);
        $this->assertCount(2, $updatedAchievements);

        $this->assertArrayHasKey("first-blood", $updatedAchievements);
        $achieved = $updatedAchievements['first-blood'];

        $this->assertArrayHasKey("achievementId", $achieved);
        $this->assertArrayHasKey("userId", $achieved);
        $this->assertArrayHasKey("completionDateTime", $achieved);
        $this->assertArrayHasKey("progress", $achieved);

        $this->assertEquals("first-blood", $achieved["achievementId"]);
        $this->assertEquals(1, $achieved["userId"]);
        $this->assertContains(date("Y-m-d H:i"), $achieved["completionDateTime"]);
        $this->assertEquals(100, $achieved["progress"]);
    }

    public function testPersistingAchievement()
    {
        $client = $this->makeClient();

        //post an event and make sure achievement is not complete yet
        $content = <<<EOF
        {
            "tag": "player-killed",
            "userId": 1,
            "payload": {
                "killedPlayedId": 12,
                "killedPlayerName": "noob",
                "time": "2012-12-12 12:12:12"
            }     
        }
EOF;
        $client->request("POST", "/events", [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(200, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();
        $this->assertJson($responseBody);
        $update = json_decode($responseBody, true);

        $this->assertArrayHasKey("updatedAchievements", $update);
        $this->assertArrayHasKey("unhandledEvents", $update);

        $this->assertEmpty($update["unhandledEvents"]);

        $updatedAchievements = $update["updatedAchievements"];
        $this->assertInternalType("array", $updatedAchievements);
        $this->assertCount(2, $updatedAchievements);

        $this->assertArrayHasKey("kill-and-destroy", $updatedAchievements);
        $updated = $updatedAchievements['kill-and-destroy'];

        $this->assertArrayHasKey("achievementId", $updated);
        $this->assertArrayHasKey("userId", $updated);
        $this->assertArrayHasKey("completionDateTime", $updated);
        $this->assertArrayHasKey("progress", $updated);

        $this->assertEquals("kill-and-destroy", $updated["achievementId"]);
        $this->assertEquals(1, $updated["userId"]);
        $this->assertNull($updated["completionDateTime"]);
        $this->assertEquals(0, $updated['progress']);

        //post the second event - now the achievement must be complete
        $content = <<<EOF
        {
            "tag": "structure-destroyed",
            "userId": 1,
            "payload": {
                "killedPlayedId": 12,
                "killedPlayerName": "noob",
                "time": "2012-12-12 12:12:12"
            }     
        }
EOF;
        $client->request("POST", "/events", [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(200, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();
        $this->assertJson($responseBody);
        $update = json_decode($responseBody, true);

        $this->assertArrayHasKey("updatedAchievements", $update);
        $this->assertArrayHasKey("unhandledEvents", $update);

        $this->assertEmpty($update["unhandledEvents"]);

        $updatedAchievements = $update["updatedAchievements"];
        $this->assertInternalType("array", $updatedAchievements);
        $this->assertCount(1, $updatedAchievements);

        $this->assertArrayHasKey("kill-and-destroy", $updatedAchievements);
        $achieved = $updatedAchievements['kill-and-destroy'];

        $this->assertArrayHasKey("achievementId", $achieved);
        $this->assertArrayHasKey("userId", $achieved);
        $this->assertArrayHasKey("completionDateTime", $achieved);
        $this->assertArrayHasKey("progress", $achieved);

        $this->assertEquals("kill-and-destroy", $achieved["achievementId"]);
        $this->assertEquals(1, $achieved["userId"]);
        $this->assertNotNull($achieved["completionDateTime"]);
        $this->assertContains(date("Y-m-d H:i"), $achieved["completionDateTime"]);
        $this->assertEquals(100, $achieved["progress"]);
    }

    public function testInvalidPayload()
    {
        $client = $this->makeClient();

        $content = <<<EOF
        {
            "tag": "player-killed",
            "userId": 1,
            "payload": {
                "oops": "fail"
            }     
        }
EOF;

        $client->request("POST", "/events", [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(200, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();
        $this->assertJson($responseBody);
        $update = json_decode($responseBody, true);

        $this->assertArrayHasKey("updatedAchievements", $update);
        $this->assertArrayHasKey("unhandledEvents", $update);

        //first-blood processor doesn't care about validation
        $this->assertInternalType("array", $update["updatedAchievements"]);
        $this->assertCount(1, $update["updatedAchievements"]);

        //kill-and-destroy processor will cry about invalid payload
        $this->assertInternalType("array", $update["unhandledEvents"]);
        $this->assertCount(1, $update["unhandledEvents"]);

        $unhandled = $update["unhandledEvents"][0];

        $this->assertArrayHasKey("originalEvent", $unhandled);
        $this->assertArrayHasKey("reason", $unhandled);

        $original = $unhandled['originalEvent'];
        $this->assertArrayHasKey("tag", $original);
        $this->assertArrayHasKey("userId", $original);
        $this->assertArrayHasKey("payload", $original);
        $this->assertEquals("player-killed", $original['tag']);
        $this->assertEquals(1, $original["userId"]);
        $this->assertNotEmpty($original['payload']);

        $reason = $unhandled['reason'];
        $this->assertArrayHasKey("message", $reason);
        $this->assertArrayHasKey("validationErrors", $reason);
        $this->assertContains("Invalid payload", $reason['message']);
    }

    public function testInvalidJson()
    {
        $client = $this->makeClient();

        $content = <<<EOF
        {
            {{{{{{oops
        }
EOF;

        $client->request("POST", "/events", [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(400, $client);
    }

    public function testMissingFields()
    {
        $client = $this->makeClient();

        $content = <<<EOF
        {

        }
EOF;
        $client->request("POST", "/events", [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(400, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();
        $this->assertJson($responseBody);
        $error = json_decode($responseBody, true);

        $this->assertArrayHasKey("message", $error);
        $this->assertArrayHasKey("validationErrors", $error);
        $this->assertContains("Validation failed", $error['message']);

        $validationErrors = $error['validationErrors'];
        $this->assertArrayHasKey("tag", $validationErrors);
        $this->assertArrayHasKey("userId", $validationErrors);

        $this->assertContains("Event tag is required", $validationErrors['tag']);
        $this->assertContains("User id is required", $validationErrors['userId']);
    }

    public function testNoHandler()
    {
        $client = $this->makeClient();

        $content = <<<EOF
        {
            "tag": "debug-event-123123123@@@@",
            "userId": "@TEST@",
            "payload": {
                
            }     
        }
EOF;

        $client->request("POST", "/events", [], [], ['CONTENT_TYPE' => 'application/json'], $content);
        $this->assertStatusCode(200, $client);

        $response = $client->getResponse();
        $responseBody = $response->getContent();
        $this->assertJson($responseBody);
        $update = json_decode($responseBody, true);

        $this->assertArrayHasKey("updatedAchievements", $update);
        $this->assertArrayHasKey("unhandledEvents", $update);

        $this->assertEmpty($update["updatedAchievements"]);

        $this->assertInternalType("array", $update["unhandledEvents"]);
        $this->assertCount(1, $update["unhandledEvents"]);

        $unhandled = $update["unhandledEvents"][0];

        $this->assertArrayHasKey("originalEvent", $unhandled);
        $this->assertArrayHasKey("reason", $unhandled);
        $this->assertArrayHasKey("message", $unhandled['reason']);

        $this->assertContains("No handler for tag", $unhandled['reason']['message']);
    }
}

