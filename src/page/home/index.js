const SELECT_corso_laurea = document.querySelector("#form-input #corso_laurea");
const DATE_data_laurea = document.querySelector("#form-input #data_laurea");
const TEXTAREA_matricole = document.querySelector("#form-input #matricole");
const BUTTON_crea_prospetti = document.querySelector("#form-input #crea_prospetti");
const BUTTON_apri_prospetti = document.querySelector("#form-input #apri_prospetti");
const BUTTON_invia_prospetti = document.querySelector("#form-input #invia_prospetti");

function showToast(message, error=false)
{
    let toast = document.createElement('div');
    toast.id = 'toast';

    time = message.split(" ").length * 1000 + 5000 * error ;

    toast.appendChild(document.createTextNode(message));
    document.body.appendChild(toast);

    setTimeout(function () {
        toast.classList.add("active", error ? "error" : null);
        setTimeout(function () {
            toast.classList.remove("active");
            setTimeout(function () {
                document.body.removeChild(toast);
            }, time + 500);
        },time);
    }, 500);
}

document.addEventListener("DOMContentLoaded", () => {
    fetch("?api=GETCorsiDiLaurea")
    .then((res) => res.json())
    .then((res) => {

        res.forEach((elem) => {
            let option = new Option(elem.CdL, elem["CdL-short"]);
            SELECT_corso_laurea.add(option, undefined);
        });

        DATE_data_laurea.min = new Date().toISOString().split("T")[0];

        BUTTON_crea_prospetti.disabled = false;
        DATE_data_laurea.disabled = false;
        TEXTAREA_matricole.disabled = false;
        SELECT_corso_laurea.disabled = false;

    });
});

BUTTON_crea_prospetti.addEventListener("click", (e) => {

    if (!SELECT_corso_laurea.checkValidity()) {
        SELECT_corso_laurea.reportValidity();
        return;
    }

    if (!DATE_data_laurea.checkValidity()) {
        DATE_data_laurea.reportValidity();
        return;
    }

    if (!TEXTAREA_matricole.checkValidity()) {
        TEXTAREA_matricole.reportValidity();
        return;
    }

    fetch("?api=POSTCreaProspetti", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            corso_laurea: SELECT_corso_laurea.value,
            data_laurea: DATE_data_laurea.value,
            matricole: TEXTAREA_matricole.value.split("\n").map((elem) => parseInt(elem.trim()))
        })
    })
    .then((res) => res.ok ? res.json() : Promise.reject(res))
    .then((res) => showToast(res.message))
	.catch((err) => err.json())
	.then((err) => showToast(err.message, true));
});

