const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber
}


describe("Creates a Mobile Order", () => {

    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })
    // it.only("Creates an Account", () => {
    //     cy.visit("/")
    //     cy.get("#create-tab").click()
    //     cy.get("#link-to-create-account").click()
    //     cy.get("#account-name").type("Conta do Zé")
    //     cy.get("#account-reference").type("ZE123>")
    //     cy.get("#save-account-button").click()
    //     cy.get(".notyf").contains("Created with Success")
    // })

    // it("Creates Mobile Order", () => {
    //     cy.visit("/")
    //     cy.get("#create-tab").click()
    //     cy.get("#account_id").select("Conta do Zé")
    //     cy.get("#solution_id").select("Mobile")
    //     cy.get("#btn-create-quote").click()
    //     cy.get("#btn-plan-0-add-25").click()
    //     cy.get("#btn-plan-1-add-25").click()
    //     cy.get("#btn-step-1-next").click()
    //     cy.get("#btn-step-2-previous").click()
    //     cy.get("#btn-plan-2-add-50").click()
    //     cy.get("#btn-step-1-next").click()
    //     cy.get("#input-bolt-0-quantity").type(30)
    //     cy.get("#btn-step-2-next").click()

    //     cy.get("#hardware-0-stock").then(($input) => {
    //         const spanText = $input.text().trim(); // Get the text content from the <span> and trim any extra whitespace

    //         // Perform conditional logic based on the span text
    //         if (spanText > 0) {
    //             cy.get("#input-hardware-0-quantity").type(spanText)
    //         }
    //     });

    //     cy.get("#btn-step-3-next").click()
    // })
    it("I can change margin and Discount from recurring", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()

        let items = ["recurring", "one_off"]

        items.forEach((key) => {
            let total = null
            cy.get("#" + key + "_total").then(($title) => {
                total = $title.text().trim();
            });

            let line1 = { qty: null, discount: null, margin: null }
            cy.get("#btn-" + key + "-0-edit").click()
            cy.get("#input-" + key + "-0-quantity").then(($input) => {
                line1.qty = $input.val().trim();
            }).clear().type(rand(line1.qty));
            cy.get("#input-" + key + "-0-discount").then(($input) => {
                line1.discount = $input.val().trim();
            }).clear().type(rand(line1.discount, 11));
            cy.get("#input-" + key + "-0-margin").then(($input) => {
                line1.margin = $input.val().trim();
            }).clear().type(rand(line1.margin, 21));


            cy.get("#btn-" + key + "-0-save").click()
            cy.get("#btn-" + key + "-0-edit").click()
            cy.get("#input-" + key + "-0-quantity").invoke("val") // Get the input value
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.qty); // Assert that it is NOT equal to "Unexpected Text"
                });
            cy.get("#input-" + key + "-0-discount").invoke("val") // Get the input value
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.discount); // Assert that it is NOT equal to "Unexpected Text"
                });
            cy.get("#input-" + key + "-0-margin").invoke("val") // Get the input value
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.margin); // Assert that it is NOT equal to "Unexpected Text"
                });

            cy.get("#" + key + "_total").invoke("text") // Get the input value
                .then((text) => {
                    expect(text).to.not.equal(total); // Assert that it is NOT equal to "Unexpected Text"
                });
        })


    })
});

