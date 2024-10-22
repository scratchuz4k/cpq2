describe("Creates a Mobile Order", () => {

    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })

    it("Dashboard contains no orders", () => {
        cy.visit("/")
        cy.contains("No records found")
    })

    it("Creates an Account",() => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type("Conta do Zé")
        cy.get("#account-reference").type("ZE123>")
        cy.get("#save-account-button").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it("Creates Mobile Order", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select("Conta do Zé")
        cy.get("#solution_id").select("Mobile")
        cy.get("#btn-create-quote").click()
        cy.get("#btn-plan-0-add-25").click()
        cy.get("#btn-plan-1-add-25").click()
        cy.get("#btn-step-1-next").click()
        cy.get("#btn-step-2-previous").click()
        cy.get("#btn-plan-2-add-50").click()
        cy.get("#btn-step-1-next").click()
        cy.get("#input-bolt-0-quantity").type(30)
        cy.get("#btn-step-2-next").click()

        cy.get('#hardware-0-stock').then(($input) => {
            const spanText = $input.text().trim(); // Get the text content from the <span> and trim any extra whitespace

            // Perform conditional logic based on the span text
            if (spanText > 0) {
                cy.get("#input-hardware-0-quantity").type(spanText)
            }
        });

        cy.get("#btn-step-3-next").click()
    })
});
