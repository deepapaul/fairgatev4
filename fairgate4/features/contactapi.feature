Feature: Test send API request
In order to test my API
As a Tester
I want to be able to perform HTTP request

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/members.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 401
        When I have a request "GET /api/contacts/fed/2/members.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic c3"
        And I request "GET /api/contacts/fed/2/members.xml"
        Then the response status code should be 401

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET /api/contacts/fed/2/members.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/8/members.xml"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/members.xml?lmod=100"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lmod=100"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET /api/contacts/fed/2/members.xml?lmod=100"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/8/members.xml?lmod=100"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/members.xml?lmod=540"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lmod=540"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/members.xml?count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?count=1"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET /api/contacts/fed/2/members.xml?count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/8/members.xml?count=1"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/members.xml?lmod=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lmod=100&count=1"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET /api/contacts/fed/2/members.xml?lmod=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/8/members.xml?lmod=100&count=1"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/members.xml?lmod=800&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lmod=800&count=1"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET api/contacts/fed/2/members/92558.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET api/contacts/fed/2/members/92558.xml"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET api/contacts/fed/8/members/18865.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET api/contacts/fed/8/members/18865.xml"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET api/contacts/fed/2/members/1886.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members/1886.xml"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/2/members.xml"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lmod=100"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lmod=100"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lmod=100"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/2/members.xml?lmod=100"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lmod=800"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lmod=800"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?count=1"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/2/members.xml?count=1"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lmod=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lmod=100&count=1"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lmod=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/2/members.xml?lmod=100&count=1"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lmod=900&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lmod=900&count=1"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET api/contacts/fed/2/club/144/members/41376.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET api/contacts/fed/2/club/144/members/41376.xml"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET api/contacts/fed/2/club/2/members/41376.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET api/contacts/fed/2/club/2/members/41376.xml"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET api/contacts/fed/2/club/144/members/476.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET api/contacts/fed/2/club/144/members/476.xml"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/members.xml?lcrt=100"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lcrt=100"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/members.xml?lcrt=500"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lcrt=500"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=100"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=100"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=100"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/2/members.xml?lcrt=100"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=800"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=800"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/clubs.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/clubs.xml"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET /api/contacts/fed/8/clubs.xml"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/8/clubs.xml"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/members.xml?lcrt=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lcrt=100&count=1"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET /api/contacts/fed/8/members.xml?lcrt=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/8/members.xml?lcrt=100&count=1"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/members.xml?lcrt=10000&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?lcrt=10000&count=1"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=100&count=1"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=100&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/2/members.xml?lcrt=100&count=1"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=10000&count=1"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?lcrt=10000&count=1"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/members.xml?sdate=2016-12-10 12:12:12"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?sdate=2016-12-10 12:12:12"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403
        When I have a request "GET /api/contacts/fed/2/members.xml?sdate=2016-12-10 12:12:12"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/8/members.xml?sdate=2016-12-10 12:12:12"
        Then the response status code should be 403

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/members.xml?sdate=2016-12-10 12:12"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/members.xml?sdate=2016-12-12"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 200
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?sdate=2016-12-10 12:12:12"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?sdate=2016-12-10 12:12:12"
        Then the response status code should be 200

    Scenario: Sending GET request to contact listing api to verify whether the response code is 404
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?sdate=2016-12-10 12:12:12"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/2/members.xml?sdate=2016-12-10 12:12:12"
        Then the response status code should be 404

    Scenario: Sending GET request to contact listing api to verify whether the response code is 500
        When I have a request "GET /api/contacts/fed/2/club/144/members.xml?sdate=2016-12-10 12:12"
        And I set the "Accept" header to "application/xml"
        And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
        And I request "GET /api/contacts/fed/2/club/144/members.xml?sdate=2016-12-12"
        Then the response status code should be 500

    Scenario: Sending GET request to contact listing api to verify whether the response code is 403 when fed id is wrong
       When I have a request "GET /api/contacts/fed/7/members.xml?lcrt=100"
       And I set the "Accept" header to "application/xml"
       And I set the "Authorization" header to "Basic cmliY29zaW51czpJQlFUYVZWbnV3aVFUYnR4R1NTZA=="
       And I request "GET /api/contacts/fed/7/members.xml?lcrt=100"
       Then the response status code should be 403