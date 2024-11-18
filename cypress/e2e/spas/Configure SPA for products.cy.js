import { generateHash, getCurrentDate } from "../../support/utils.js";

const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }
const price = { install: null, price: null }

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

    it('Get Price From Gamma Product', () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Connectivity - Ethernet & Fibre")
        cy.get("#input_address").type('EH32 9NT')
        cy.get("#select_address").should('be.visible').select(1)
        cy.get("#btn-create-quote").should('not.be.disabled').click()

        //Provider filter
        cy.get('#provider-multiselect').click()
        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
        })

        cy.wait(3000)

        cy.get("#line-0-price-install").then(($input) => {
            price.install = $input.text().trim();
            cy.log(price.install)
        })
        cy.get("#line-0-price-monthly").then(($input) => {
            price.monthly = $input.text().trim();
            cy.log(price.monthly)
        })
    })
    it('Create Supplier Gamma Products', () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-suppliers").click()
        cy.get("#create-suppliers").click()
        cy.get("#supplier-name").type("Gamma")
        cy.get("#supplier-reference").type("Gamma")

    })
    it('Create SPA Gamma Products', () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-spas").click()
        cy.get("#create-spa").click()
        cy.get("#spa-entity-Supplier").click()
    })
})
