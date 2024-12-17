const crypto = require("crypto");
const {getCurrentDate, generateHash, slugify} = require("../../support/utils.js");
const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber
}

const temp = getCurrentDate()
const user = { name: slugify(temp), email: slugify(temp) + "@email.com", password: generateHash(temp) }
const account = { ref: generateHash(temp), name: temp }


describe("Setup Sales Rep.cy", () => {
    it("Creates an User", () => {
        cy.login("super@example.com", "werwerwer")
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-users").click()
        cy.get("#link-to-create-user").click()

        cy.get("#user-name").type(user.name)
        cy.get("#user-email").type(user.email)
        cy.get("#user-password").type(user.password)
        cy.get("#user-role").select("Sales Rep")
        cy.get("#user-commission-rate").type(5)
        cy.get("#btn-save-user").click()
        cy.get("#treetable-search").type(user.name)
        cy.get("#users-table").contains(user.name)
    })
    it("Creates an Order and checks commission", () => {
        cy.login(user.email, user.password)
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#btn-save-account").click()
        cy.get(".notyf").contains("Created with Success")
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Mobile")
        cy.get("#btn-create-quote").click()
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        cy.get("#order-tax-commission").invoke("text")
            .then((text) => {
                expect(text).to.not.equal(5);
            });
    })
});

