package dao;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class OrderDAO {

    public void createOrder(String orderDate, double total) throws SQLException {
        String sql = "INSERT INTO orders (order_date, total) VALUES (?, ?)";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, orderDate);
            stmt.setDouble(2, total);
            stmt.executeUpdate();
        }
    }

    public List<String> getOrders() throws SQLException {
        List<String> orders = new ArrayList<>();
        String sql = "SELECT * FROM orders";
        try (Connection conn = DBConnection.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                orders.add("ID: " + rs.getInt("id") +
                           ", Order Date: " + rs.getString("order_date") +
                           ", Total: " + rs.getDouble("total"));
            }
        }
        return orders;
    }
}
