//FUNÇÕES PARA DEMO

function method() {

	$.ajax({
		type: "POST",
		url: "/method.php",
		data: "data=" + $("#data").val() + "&method=" + $("#method").val() + "&emitter=" + $("#emitter").val(),
		dataType: "json",
		success: function (retorno) {

			if ($("#emitter").val() == "") {
				$("#emitter").val(retorno.DADO_EMITIDO);
			}
			$("#receiver").val(retorno.DADO_RECEBIDO);

			$("#note").html(retorno.OBSERVACAO);

			if (retorno.DADO_IS_VALID) {

				$("#result").html("<b>ERROS ENCONTRADOS</b>");

				$("#panel").removeClass("panel-default");
				$("#panel").addClass("panel-danger");
				$("#panel").removeClass("panel-success");

				$("#body").removeClass("bg-warning");
				$("#body").addClass("bg-danger");
				$("#body").removeClass("bg-success");

			} else {

				$("#result").html("<b>SUCESSO NA OPERAÇÃO</b>");

				$("#panel").removeClass("panel-default");
				$("#panel").addClass("panel-success");
				$("#panel").removeClass("panel-danger");

				$("#body").removeClass("bg-warning");
				$("#body").addClass("bg-success");
				$("#body").removeClass("bg-danger");

			}

		},
		error: function (XMLHttpRequest, textStatus, errorThrown) {
			console.log("Ocorreu um erro durante a chamada !");
		}
	});

	return false;

};

function acceptedformat(type) {

	switch (type) {
		case "01":
			$("#data").mask("XXXXXXX", {
				translation: {
					"X": {
						pattern: /[0-1]/
					}
				},
				placeholder: "0101010"
			});
			break;
		case "02":
			$("#data").mask("XXXXXXX", {
				translation: {
					"X": {
						pattern: /[0-1]/
					}
				},
				placeholder: "0101010"
			});
			break;
		default:
			$("#data").mask("XXXXXXX", {
				translation: {
					"X": {
						pattern: /[0-1]/
					}
				},
				placeholder: "0101010"
			});
		case "03":
			$("#data").mask("XXXXXX", {
				translation: {
					"X": {
						pattern: /[0-1]/
					}
				},
				placeholder: "010101"
			});
			break;
		default:
			$("#data").mask("XXXXXXX", {
				translation: {
					"X": {
						pattern: /[0-1]/
					}
				},
				placeholder: "0101010"
			});
	}

};

$("#data").mask("XXXXXXX", {
	translation: {
		"X": {
			pattern: /[0-1]/
		}
	},
	placeholder: "0101010"
});