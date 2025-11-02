package dao;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ProductDAO {

    public void createProduct(String name, double price, int stock) throws SQLException {
        String sql = "INSERT INTO products (name, price, stock) VALUES (?, ?, ?)";
        try (Connection conn = DBConnection.getConnection();
             PreparedStatement stmt = conn.prepareStatement(sql)) {
            stmt.setString(1, name);
            stmt.setDouble(2, price);
            stmt.setInt(3, stock);
            stmt.executeUpdate();
        }
    }

    public List<String> getProducts() throws SQLException {
        List<String> products = new ArrayList<>();
        String sql = "SELECT * FROM products";
        try (Connection conn = DBConnection.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            while (rs.next()) {
                products.add("ID: " + rs.getInt("id") +
                             ", Name: " + rs.getString("name") +
                             ", Price: " + rs.getDouble("price") +
                             ", Stock: " + rs.getInt("stock"));
            }
        }
        return products;
    }
}
