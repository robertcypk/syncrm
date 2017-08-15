<html>
<head>
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<head>
<body>


	<script>
	$.ajax({
    type: "POST",
    url: "http://localhost/Workflow/api/instances",
    // The key needs to match your method's input parameter (case-sensitive).
    data: JSON.stringify({ 
		"Initiator":{
			"InitiatorName":"initiator"
		},
		"InstanceCreationFlags":0,
		"Origin":"String content",
		"Originator":"String content",
		"Timestamp":"\/Date(928113600000+1000)\/",
		"WorkflowName":"workflow_centrum",
		"ParameterCollection":[{
		"IsDebug":true,
		"Name":"userid",
		"Value":'1'
		}]
	}),
	contentType: "application/json; charset=utf-8",
    dataType: "json"

});
	</script>
</body>
</html>