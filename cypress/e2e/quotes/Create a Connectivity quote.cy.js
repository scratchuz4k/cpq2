import {generateHash, getCurrentDate} from "../../support/utils.js";

const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })

    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#save-account-button").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it('Create a Connectivity order', () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Connectivity - Ethernet & Fibre")
        cy.get("#input_address").type('EH32 9NT')
        cy.get("#select_address").should('be.visible').select(1)
        cy.get("#btn-create-quote").should('not.be.disabled').click()

        //Provider filter
        cy.get('.p-multiselect').eq(0).click()

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
        })

        //SubProvider filter
        cy.get('.p-multiselect').eq(1).click()

        cy.get('.p-multiselect-list-container').eq(1).within(() => {
            cy.get('.p-checkbox').eq(1).click()
        })

        cy.get('.cpq-progress-bar').eq(0).should('be.visible')
        cy.wait(3000)

        //Add products to quote
        let keys = [0, 1, 2, 3, 4]
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click()
            cy.get('tbody tr button').eq(key).contains('Added')
        })

        //Save quote
        cy.get('.continue-btn').eq(0).click()
        cy.get('.recurring-item').its('length').should('eq', 5);
        cy.get('.one_off-item').its('length').should('eq', 5);
    })
})
