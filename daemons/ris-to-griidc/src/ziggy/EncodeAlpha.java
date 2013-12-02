package ziggy;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;

public class EncodeAlpha {

	public static int Base = 'A';

	public EncodeAlpha() {
	}

	public static int encode(char c) {
		char c2 = c;
		if (Character.isLetter(c)) {
			c2 = Character.toUpperCase(c);
		}
		return (c2 - Base) + 1;
	}

	public static boolean isALetter(char c) {
		return Character.isLetter(c);
	}

	public static void main(String[] args) {

		BufferedReader reader = new BufferedReader(new InputStreamReader(
				System.in));
		String line = null;
		boolean keepAtIt = true;
		while (keepAtIt) {
			try {
				System.out.print("\nPlease enter a character (number to quit) : ");
				line = reader.readLine();
				char c = line.charAt(0);
				if (Character.isDigit(c)) {
					keepAtIt = false;
					System.out.println("Good By");
				} else if (EncodeAlpha.isALetter(c)) {
					int v = EncodeAlpha.encode(c);
					System.out.println(" " + c + " is " + v);
				} else {
					System.err.println("Sorry - not a letter.");
				}
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}

}
