Feature: Test send API request  [Token:4514110488a51968fb830513bcc9ab8ef4c8d4e6b2416ee9 Club:(cUQ0T253PT0=/8825/Grapes)]
In order to test my API
As a Tester
I want to be able to perform HTTP request


########################################################## Initialization #####################################################################
Scenario:0.Initializing the Gotcourts API test
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the Token header to "4514110488a51968fb830513bcc9ab8ef4c8d4e6b2416ee9" for club "8825"
        And I set the "Accept" header to "application/json"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 403
        And the error message should be "Forbidden"

########################################################## Verify Club Token #####################################################################
Scenario:1.1 Sending GET request to verify club token api to verify whether the response code is 200 when club has not activated that token yet and no other club is using that token
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 200
		
Scenario:1.2 Sending GET request to verify club token api to verify whether the response code is 401 when 'X-Client-Token' is missing
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:1/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 404
        And the error message should be "Token not found"
		
Scenario:1.3 Sending GET request to verify club token api to verify whether the response code is 401 when 'X-Client-Token' is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "yuguygihihtyyuguygug"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 404
        And the error message should be "Token not found"
		
Scenario:1.4 Sending GET request to verify club token api to verify whether the response code is 401 when 'X-Client-Token' is canceled
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "0e4afeb9e33ab7722fff9f8007d0614b6c2b6b7816c6c5c92b6e3dd92448e272987f9a05a193a84f1544809620be1db4"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 404
        And the error message should be "Token not found"
		
Scenario:1.5 Sending GET request to verify club token api to verify whether the response code is 403 when 'X-Tenant-Token' is missing
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 403
        And the error message should be "Forbidden"
		
Scenario:1.6 Sending GET request to verify club token api to verify whether the response code is 403 when 'X-Tenant-Token' is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 403
        And the error message should be "Forbidden"
		
Scenario:1.7 Sending GET request to verify club token api to verify whether the response code is 404 when clubid is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T=/clubtoken"
        Then the response status code should be 404
        And the error message should be "Club not found"
		
Scenario:1.8 Sending GET request to verify club token api to verify whether the response code is 404 when fedid is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/4/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 404
        And the error message should be "Federation not found"
		 
Scenario:1.9 Sending GET request to verify club token api to verify whether the response code is 409 when token is already in use (Before activation)
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 200

##################################################### Activate Club after Registration #####################################################################
Scenario:2.1 Sending GET request to activate club after registration api to verify whether the response code is 403 when 'X-Tenant-Token' is missing
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/activateclub?token=4514110488a51968fb830513bcc9ab8ef4c8d4e6b2416ee9"
        Then the response status code should be 403
        And the error message should be "Forbidden"
                
Scenario:2.2 Sending GET request to activate club after registration api to verify whether the response code is 403 when 'X-Tenant-Token' is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/activateclub?token=4514110488a51968fb830513bcc9ab8ef4c8d4e6b2416ee9"
        Then the response status code should be 403
        And the error message should be "Forbidden"
                
Scenario:2.3 Sending GET request to activate club after registration api to verify whether the response code is 404 when clubid is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ/activateclub?token=4514110488a51968fb830513bcc9ab8ef4c8d4e6b2416ee9"
        Then the response status code should be 404
        And the error message should be "Club not found"
                
Scenario:2.4 Sending GET request to activate club after registration api to verify whether the response code is 404 when fedid is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/2/club/cUQ0T253PT0=/activateclub?token=4514110488a51968fb830513bcc9ab8ef4c8d4e6b2416ee9"
        Then the response status code should be 404
        And the error message should be "Federation not found"
                
Scenario:2.5 Sending GET request to activate club after registration api to verify whether the response code is 500 when tokenhash is empty
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/activateclub?token="
        Then the response status code should be 500
        And the error message should be "Token not found" with error code 1005
                
Scenario:2.6 Sending GET request to activate club after registration api to verify whether the response code is 500 when tokenhash is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/activateclub?token=85"
        Then the response status code should be 500
        And the error message should be "Token not found" with error code 1005
                
Scenario:2.7 Sending GET request to activate club after registration api to verify whether the response code is 500 when cancelled tokenhash is used
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/activateclub?token=0104bab4aa18112ec2b65434711df402"
        Then the response status code should be 500
        And the error message should be "Token not found" with error code 1005

Scenario:2.8 Sending GET request to activate club after registration api to verify whether the response code is 200
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/activateclub?token=c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        Then the response status code should be 200
                
                
Scenario:2.9 Sending GET request to activate club after registration api to verify whether the response code is 409 when resent the same request after activation
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/activateclub?token=:tokenhash"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/activateclub?token=c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        Then the response status code should be 409
        And the error message should be "Token already in use"

Scenario:1.10 Sending GET request to verify club token api to verify whether the response code is 409 when token is already in use (After activation)
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/clubtoken"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/clubtoken"
        Then the response status code should be 409
        And the error message should be "Token already in use"

##################################################### Get Playercategories   ########################################################       
Scenario:3.1 Sending GET request to get player categories api to verify whether the response code is 200 and player category details are listed 
        When I have a request "GET /api/gotcourts/contacts/fed/0/club//categories?lmod=:value"
        And I set the "Accept" header to "application/json"
	And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 200
        And the response should have the category fields "categoryidhash,categoryname,date,value_before,value_after" for categoryid "b1RJUG1RPT0="
        
Scenario:3.2 Sending GET request to get player categories api to verify whether the response code is 200 and fed memebership and club membership details are listed in subfed club  when c1 is ON
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
	And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 200
        And membership count should be 12
		
Scenario:3.3 Sending GET request to get player categories api to verify whether the response code is 200 and fed memebership and club membership details are listed in subfed club  when c1 is ON
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
	And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=1"
        Then the response status code should be 200
        And membership count should be 0
     
Scenario:3.4 Sending GET request to get player categories api to verify whether the response code is 200 and the last updation value is shown in value_before field if there are multiple updation
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
	And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 200
        And the category field "value_before" should be "Current Players" for categoryid "b1RJUG1RPT0="

Scenario:3.5 Sending GET request to get player categories api to verify whether the response code is 200 and the category name is in given language
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "Accept-language" header to "fr"
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I set the "Accept-language" header to "fr"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=3"
        Then the response status code should be 200
        And the category "b1RJUGtnPT0=" should be listed

Scenario:3.6 Sending GET request to get player categories api to verify whether the response code is 401 when 'X-Client-Token' is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "52"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 401
        And the error message should be "Not authorized"
		
Scenario:3.7 Sending GET request to get player categories to verify whether the response code is 401 when 'X-Client-Token' is canceled
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "04e9c7bd41487c9c7832e12563064ec834d6dc61559c548774281528a4484c94159ddf2a7c9dc00913cc26a9ee41b7f6"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 401
        And the error message should be "Not authorized"
		
Scenario:3.8 Sending GET request to get player categories api to verify whether the response code is 403 when 'X-Tenant-Token' is missing
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 403
        And the error message should be "Forbidden"
		
Scenario:3.9 Sending GET request to get player categories to verify whether the response code is 403 when 'X-Tenant-Token' is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 403
        And the error message should be "Forbidden"
		
Scenario:3.10 Sending GET request to get player categories to verify whether the response code is 404 when clubid is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cU/categories?lmod=360"
        Then the response status code should be 404
        And the error message should be "Club not found"
		
Scenario:3.11 Sending GET request toget player categories api to verify whether the response code is 404 when fedid is invalid
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/7/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 404
        And the error message should be "Federation not found"
		
Scenario:3.12 Sending GET request to player categories api to verify whether the response code is 500 when lmod is 0
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=0"
        Then the response status code should be 500
        And the error message should be "Lmod should be greater than zero and less than 360" with error code 1001
		
Scenario:3.13 Sending GET request to player categories api to verify whether the response code is 500 when lmod is 361
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=361"
        Then the response status code should be 500
        And the error message should be "Lmod should be greater than zero and less than 360" with error code 1001

Scenario:3.14 Sending GET request to player categories api to verify whether the response code is 500 when lmod is empty
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories"
        Then the response status code should be 500
        And the error message should be "The Lmod should be specified for this api" with error code 1007

Scenario:3.15 Sending GET request to get player categories api to verify whether the response code is 200 and date,value_before and value_after fields values are empty for newly created categories
        When I have a request "GET /api/gotcourts/contacts/fed/:fid/club/:cid/categories?lmod=:value"
        And I set the "Accept" header to "application/json"
        And I set the "Authorization" header to "Basic Z290Y291cnRzOmRlV2Q1VXd2QmN3ZVo4RmtSNmVDZ21QVA=="
        And I set the "X-Tenant-Token" header to "0125ee8dfe42bafbec95aa0d2676c91d8a780715b76504cf798aae6e74c08a30"
        And I set the "X-Client-Token" header to "c4016f0c71ab2e531f09e50962858323655f4485185f01381eddaf7755afc488b32eb6306096f713f49a845326331316"
        And I request "GET /api/gotcourts/contacts/fed/0/club/cUQ0T253PT0=/categories?lmod=360"
        Then the response status code should be 200
        And field date should be empty for "b1RJRW5BPT0="
        And field value_before should be empty for "b1RJRW5BPT0="
        And field value_after should be empty for "b1RJRW5BPT0="