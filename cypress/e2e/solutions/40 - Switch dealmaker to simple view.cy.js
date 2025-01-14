const { getCurrentDate, generateHash } = require("../../support/utils.js");

describe("Creates a Mobile Quote", () => {

    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })

    it("Formats connectivity table to simple view", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-apis").click()
        cy.get("#datatable-btn-0-edit").click()
        cy.get("#1-1").click()
        cy.get("#solution_type-component").click()
        cy.wait(500)
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get(".p-select-option").eq(1).click()
        })

        cy.get("#btn-save-solution-type").click()
        cy.get(".notyf").contains("Updated with Success")
    })
    it("Creates Connectivity quote and checks table ", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(1)
        cy.get("#solution_id").select("Connectivity - Ethernet")
        cy.get("#input_address").type('EH32 9NT')
        cy.get("#select_address").should('be.visible').select(1)
        cy.get("#btn-create-quote").should('not.be.disabled').click()
        // Scroll back to top
        cy.scrollTo('top');
        //Provider filter
        cy.get('.p-multiselect').eq(0).click()

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
        })

        //SubProvider filter
        cy.get('.p-multiselect').eq(1).click()
        cy.wait(500)

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(1).click()
        })

        //Add products to quote
        let keys = [0, 1, 2]
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click()
            cy.get('tbody tr button').eq(key).contains('Added')
        })
        //Save quote
        cy.get('.continue-btn').eq(0).click()
        cy.get('.Simple-item').its('length').should('eq', 6);
        cy.get("#app > main > div > div.row.mb-4 > div > table > thead").contains("Price Plan")
        cy.get("#app > main > div > div.row.mb-4 > div > table > thead").contains("Contract Length")
        cy.get("#app > main > div > div.row.mb-4 > div > table > thead").contains("Price")
        cy.get("#app > main > div > div.row.mb-4 > div > table > thead").contains("Margin")
        cy.get("#app > main > div > div.row.mb-4 > div > table > thead").contains("Discount")
        cy.get("#app > main > div > div.row.mb-4 > div > table > thead").contains("Total")
    })

});

