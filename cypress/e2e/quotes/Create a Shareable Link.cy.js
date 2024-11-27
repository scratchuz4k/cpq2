const crypto = require("crypto");
const { getCurrentDate, generateHash } = require("../../support/utils.js");
const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber
}

const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }
let url = ''


describe("Creates a Mobile Quote", () => {
    let hardwareCount = 0;

    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })
    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#btn-save-account").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it("Creates Mobile Quote", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Mobile")
        cy.get("#btn-create-quote").click()
        cy.get("#btn-plan-0-add-25").click()
        cy.get("#btn-plan-1-add-25").click()
        cy.get("#btn-step-1-next").click()
        cy.wait(5000)
        let hardwareCount = 0
        let keys = [0, 1, 2, 3, 4]
        keys.forEach((key) => {
            cy.get(`#hardware-${key}-stock`).then(($input) => {
                const spanText = parseInt($input.text().trim()); // Get the text content from the <span> and trim any extra whitespace

                // Perform conditional logic based on the span text
                if (spanText > 0) {
                    hardwareCount += 1
                    cy.log(`Click count: ${hardwareCount}`);
                    cy.get(`#input-hardware-${key}-quantity`).type(1)
                }
            });
        })

        cy.get("#btn-step-2-next").click()

        cy.get(".recurring-item").should("have.length", 2)
        cy.get(".one_off-item").then(($items) => {
            cy.then(() => {
                expect(hardwareCount).to.equal($items.length);
            });
        })
    })
    it("I can create the link", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        cy.get("#btn-shareable-open").click()
        cy.get("#btn-shareable-create").click()
        cy.get(`#input-shareable-link`)
            .invoke('val')
            .then((value) => {
                cy.log(value)
                url = value;
            });
    })

    it("I can create the link", () => {
        cy.log(url)
        cy.visit(url)
        cy.get('.item-row').its("length")
            .then((items) => {
                expect(items).to.not.equal(0);
            });

    })

});

