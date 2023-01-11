const SELECT_corso_laurea = document.querySelector("#form-input #corso_laurea");
const DATE_data_laurea = document.querySelector("#form-input #data_laurea");
const TEXTAREA_matricole = document.querySelector("#form-input #matricole");
const BUTTON_crea_prospetti = document.querySelector("#form-input #crea_prospetti");
const BUTTON_apri_prospetti = document.querySelector("#form-input #apri_prospetti");
const BUTTON_invia_prospetti = document.querySelector("#form-input #invia_prospetti");



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
    .then((res) => res.json())
    .then((res) => console.log(res));
});

