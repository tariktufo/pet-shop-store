import dao.*;
import java.util.List;

public class Main {
    public static void main(String[] args) {
        try {
            
            UserDAO userDAO = new UserDAO();
            PetDAO petDAO = new PetDAO();
            ProductDAO productDAO = new ProductDAO();
            AppointmentDAO appointmentDAO = new AppointmentDAO();
            OrderDAO orderDAO = new OrderDAO();

            
            String email = "tarik4@example.com";
            List<String> users = userDAO.getUsers();
            boolean exists = users.stream().anyMatch(u -> u.contains(email));
            if (!exists) {
                userDAO.createUser("Tarik Tufo", email, "password123");
            }

            
            petDAO.createPet("Fido", "Dog", 3, 1); 
            productDAO.createProduct("Dog Food", 25.99, 10);
            appointmentDAO.createAppointment(1, "2025-11-02", "Pet checkup");
            orderDAO.createOrder("2025-11-02", 51.98);

            
            System.out.println("Users: " + userDAO.getUsers());
            System.out.println("Pets: " + petDAO.getPets());
            System.out.println("Products: " + productDAO.getProducts());
            System.out.println("Appointments: " + appointmentDAO.getAppointments());
            System.out.println("Orders: " + orderDAO.getOrders());

        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
